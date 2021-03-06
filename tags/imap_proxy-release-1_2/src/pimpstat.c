/*
** 
**               Copyright (c) 2002,2003 Dave McMurtrie
**
** This file is part of imapproxy.
**
** imapproxy is free software; you can redistribute it and/or modify
** it under the terms of the GNU General Public License as published by
** the Free Software Foundation; either version 2 of the License, or
** (at your option) any later version.
**
** imapproxy is distributed in the hope that it will be useful,
** but WITHOUT ANY WARRANTY; without even the implied warranty of
** MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
** GNU General Public License for more details.
**
** You should have received a copy of the GNU General Public License
** along with imapproxy; if not, write to the Free Software
** Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
**
**
**  Facility:
**
**	pimpstat.c
**
**  Abstract:
**
**	Polling Imap Mail Proxy STATistical display tool.
**
**  Authors:
**
**      Dave McMurtrie <davemcmurtrie@hotmail.com>
**
**  RCS:
**
**      $Source: /afs/pitt.edu/usr12/dgm/work/IMAP_Proxy/src/RCS/pimpstat.c,v $
**      $Id: pimpstat.c,v 1.6 2003/05/20 19:08:02 dgm Exp $
**      
**  Modification History:
**
**      $Log: pimpstat.c,v $
**      Revision 1.6  2003/05/20 19:08:02  dgm
**      Comment changes only.
**
**      Revision 1.5  2003/05/15 11:33:22  dgm
**      Patch by Ken Murchison <ken@oceana.com> to clean up build process:
**      Conditionally include <sys/param.h> instead of defining MAXPATHLEN.
**
**      Revision 1.4  2003/05/13 11:41:02  dgm
**      Patches by Ken Murchison <ken@oceana.com> to clean up build process.
**
**      Revision 1.3  2003/01/27 13:49:36  dgm
**      Added patch by Frode Nordahl <frode@powertech.no> to allow
**      compilation on Linux platforms.
**
**      Revision 1.2  2002/12/17 14:24:11  dgm
**      Added support for global configuration structure.
**
**      Revision 1.1  2002/08/30 13:32:55  dgm
**      Initial revision
**
**
**
*/


#include "imapproxy.h"
#include <errno.h>
#include <string.h>
#include <sys/mman.h>
#include <sys/types.h>
#include <sys/stat.h>
#include <fcntl.h>
#include <curses.h>
#include <time.h>
#include <strings.h>
#include <signal.h>

#if HAVE_SYS_PARAM_H
#include <sys/param.h>
#endif

#define DIGITS 11

extern WINDOW *stdscr;

static void Exit( int );
static void Handler();
static void Usage( void );


ProxyConfig_Struct PC_Struct;


/*++
 * Function:	Exit
 *
 * Purpose:	Set the terminal back to normal and then exit
 *
 * Parameters:	int -- exit code to be passed to exit()
 *
 * Returns:	nada
 *
 * Authors:	Dave McMurtrie <davemcmurtrie@hotmail.com>
 *
 * Notes:
 *--
 */
static void Exit( int ExitCode )
{
    clear();
    refresh();
    endwin();
    exit( ExitCode );
}


/*++
 * Function:	Handler
 *
 * Purpose:	SIGINT handler routine
 *
 * Parameters:	nada
 *
 * Returns:	nada
 *
 * Authors:	Dave McMurtrie <davemcmurtrie@hotmail.com>
 *
 * Notes:
 *--
 */
static void Handler()
{
    Exit( 0 );
}



int main( int argc, char *argv[] )
{
    IMAPCounter_Struct *IMAPCount;
    int fd;
    char *fn = "pimpstat";
    int i, rc;
    char ccc[DIGITS+1];
    char pcc[DIGITS+1];
    char asc[DIGITS+1];
    char psc[DIGITS+1];
    char rsc[DIGITS+1];
    char prsc[DIGITS+1];
    char tcca[DIGITS+1];
    char tcl[DIGITS+1];
    char tscc[DIGITS+1];
    char tscr[DIGITS+1];
    char ssrr[DIGITS+4];
    float Ratio;
    char stimebuf[64];
    char ctimebuf[64];
    char *CP;
    extern char *optarg;
    extern int optind;
    char ConfigFile[ MAXPATHLEN ];
    
    ConfigFile[0] = '\0';
    
    signal( SIGINT, (void (*)()) Handler );

    while (( i = getopt( argc, argv, "f:h" ) ) != EOF )
    {
	
        switch( i )
        {
	    
        case 'f':
            /* user specified a config filename */
            strncpy( ConfigFile, optarg, sizeof ConfigFile -1 );
            break;
            
        case 'h':
            Usage();
            exit( 0 );
	    
        case '?':
            Usage();
	    
            exit( 1 );
	    
        }
	
    }
    
    if ( ! ConfigFile[0] )
    {
	
        strncpy( ConfigFile, DEFAULT_CONFIG_FILE, sizeof ConfigFile -1 );
    }

    SetConfigOptions( ConfigFile );
        
    fd = open( PC_Struct.stat_filename, O_RDONLY );
    if ( fd == -1 )
    {
        printf("%s: open() failed for '%s': %s -- Exiting.\n", fn, 
               PC_Struct.stat_filename, strerror( errno ) ); 
        exit( 1 );
    }
    

    IMAPCount = ( IMAPCounter_Struct *)mmap( 0, sizeof( IMAPCounter_Struct ),
                                             PROT_READ, MAP_SHARED, fd, 0 );
    
    if ( IMAPCount == MAP_FAILED )
    {
        printf("%s: mmap() failed: %s -- Exiting.\n", fn, strerror( errno ) );
        exit( 1 );
    }
    

    stdscr = initscr();

    if ( !stdscr )
    {
	printf("%s: failed to initialize screen -- exiting.\n", fn );
	exit( 1 );
    }
    
    border( 0, 0, 0, 0, 0, 0, 0, 0 );
    mvaddstr( 2, 8, "Server Start Time:" );
    mvaddstr( 3, 2, "Last Counter Reset Time:" );
    mvaddstr( 5, 2, "CLIENT CONNECTIONS" );
    mvaddstr( 7, 5, "current:" );
    mvaddstr( 7, 40, "peak:" );
    mvaddstr( 9, 2, "ACTIVE SERVER CONNECTIONS" );
    mvaddstr( 11, 5, "current:" );
    mvaddstr( 11, 40, "peak:" );
    mvaddstr( 13, 2, "CACHED SERVER CONNECTIONS" );
    mvaddstr( 15, 5, "current:");
    mvaddstr( 15, 40, "peak:" );
    mvaddstr( 17, 2, "TOTALS" );
    mvaddstr( 19, 5, "client connections accepted:" );
    mvaddstr( 20, 5, "client logins:" );
    mvaddstr( 21, 5, "server connections created:" );
    mvaddstr( 22, 5, "server connection reuses:" );
    mvaddstr( 23, 5, "client login to server login ratio:" );
    mvaddstr( 25, 2, "CTRL-C to quit." );

    for ( ; ; )
    {
	/*
	 * I don't know crap about curses.  There's prolly an easy way to 
	 * accomplish this, but I don't know how.  Basically we have to
	 * turn all of our numbers into strings so curses can display them.
	 * I'd guess there's a printf equivalent in curses, but I dunno.
	 */

	if ( IMAPCount->TotalServerConnectionsCreated == 0 )
	{
	    snprintf( ssrr, DIGITS + 3, "          N/A" );
	}
	else
	{
	    Ratio = (float)IMAPCount->TotalClientLogins /
		(float)IMAPCount->TotalServerConnectionsCreated;
	    snprintf( ssrr, DIGITS + 3, "%9.2f : 1", Ratio );
	}
	
	/*
	 * ctime is putting a \n at the end of the string and that's
	 * making curses do goofy stuff that I don't understand.  Rather
	 * than figure out why it's breaking curses, I'm just going to
	 * copy ctime's strings into my own buffers and get rid of the
	 * \n.
	 */
	strncpy( stimebuf, ctime( &IMAPCount->StartTime ),
		 sizeof stimebuf - 1 );
	strncpy( ctimebuf, ctime( &IMAPCount->CountTime ),
		 sizeof ctimebuf - 1 );

	CP = strrchr( stimebuf, '\n' );
	if (CP)
	    *CP = '\0';

	CP = strrchr( ctimebuf, '\n' );
	if (CP)
	    *CP ='\0';
	
	snprintf( ccc, DIGITS, "%9d", IMAPCount->CurrentClientConnections );
	snprintf( pcc, DIGITS, "%9d", IMAPCount->PeakClientConnections );
	snprintf( asc, DIGITS, "%9d", IMAPCount->InUseServerConnections );
	snprintf( psc, DIGITS, "%9d", IMAPCount->PeakInUseServerConnections );
	snprintf( rsc, DIGITS, "%9d", IMAPCount->RetainedServerConnections );
	snprintf( prsc, DIGITS, "%9d", IMAPCount->PeakRetainedServerConnections );
	snprintf( tcca, DIGITS, "%9d", IMAPCount->TotalClientConnectionsAccepted );
	snprintf( tcl, DIGITS, "%9d", IMAPCount->TotalClientLogins );
	snprintf( tscr, DIGITS, "%9d", IMAPCount->TotalServerConnectionsReused );
	snprintf( tscc, DIGITS, "%9d", IMAPCount->TotalServerConnectionsCreated );
	
	mvaddstr( 2, 31, stimebuf );
	mvaddstr( 3, 31, ctimebuf );
	mvaddstr( 7, 14, ccc );
	mvaddstr( 7, 46, pcc );
	mvaddstr( 11, 14, asc );
	mvaddstr( 11, 46, psc );
	mvaddstr( 15, 14, rsc );
	mvaddstr( 15, 46, prsc );
	mvaddstr( 19, 46, tcca );
	mvaddstr( 20, 46, tcl );
	mvaddstr( 21, 46, tscc );
	mvaddstr( 22, 46, tscr );
	mvaddstr( 23, 42, ssrr );
	
	refresh();
	
        sleep( 1 );
    }
}


/*++
 * Function:    Usage
 *
 * Purpose:     Display a usage string to stdout
 *
 * Parameters:  None.
 *
 * Returns:     nada
 * 
 * Authors:     Dave McMurtrie <davemcmurtrie@hotmail.com>
 * 
 * Notes:
 *--
 */
void Usage( void )
{
    printf("Usage: pimpstat [-f config filename] [-h]\n");
    return;
}

/*
 *                            _________
 *                           /        |
 *                          /         |
 *                         /    ______|
 *                        /    /       ________
 *                       |    |        |      /
 *                       |    |        |_____/
 *                       |    |        ______
 *                       |    |        |     \
 *                       |    |        |______\
 *                        \    \_______
 *                         \           |
 *                          \          |
 *                           \_________|
 */


