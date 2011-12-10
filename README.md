GuildInfo - Simple WoW guild site
=================================

A little appp I wrote to track some info about my guild - photos, stats and achievements.

To install:

* Create a folder called <code>images</code> put photos in here
* Create a folder called <code>thumbs</code> and make sure Apache can write to it
* Copy <code>include/config.php.example</code> to <code>include/config.php</code> and modify the settings inside
* Import <code>schema.sql</code> into MySQL
* Set up the various scripts in <code>cron/</code> to be run regularly


## Important!

This is only half-working. It was originally built on the first armory 'API' (the XSLT-in-browser version). The new battlenet API does not include stats (thanks Blizzard!), so that's broken. 
Achievements don't have icons (thanks again!) and show non-faction ones (...you get the idea). Quests rely on scraping JS from wowhead, which will almost certainly break soon. It really 
needs some links to wowhead anyway, with nice tooltips. Should probably make the photos bit optional too, include a roster (and history) and do a better front page with summarized recent 
changes or something. Maybe the guild feed?
