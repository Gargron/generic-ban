## A generic ban class

Yes. For forums and stuff like that. Powered by redis. Uses PHPUnit for optional unit testing.

### Usage

    Ban::track('1.2.3.4', 1); // track user with IP 1.2.3.4 and (optional) user ID 1

    Ban::make(1, array(), 120); // ban user with the ID 1 for 2 minutes (120 seconds)

    if(Ban::is('1.2.3.4', 1)) // check if the IP 1.2.3.4 is banned, or if the optional user ID 1 is banned
    {
    	die('Nope');
    }

    Ban::undo(1); // unban the user with the ID 1

    $alts = Ban::alts(1); // get other user IDs of user with the ID 1 (alts)

    $ips = Ban::ips(1); // get the IP addresses of the user with the ID 1