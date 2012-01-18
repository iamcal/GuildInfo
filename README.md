GuildInfo - Simple WoW guild site
=================================

A little web app I wrote to track some info about my guild - photos, stat, quests and achievements.

## Installation

* Copy the files inside <code>www/</code> to your web server
* Create a folder called <code>images</code> - put photos for your gallery in here
* Create a folder called <code>thumbs</code> and make sure Apache can write to it
* Copy <code>include/config.php.example</code> to <code>include/config.php</code> and modify the settings inside
* Import <code>schema.sql</code> into MySQL
* Set up the various scripts in <code>cron/</code> to be run regularly
** `fetch.php` and `achievements_firsts.php` (run in that order) deal with achievements
** `quest_cats.php` and `quests.php` deal with quests
** `stats.php` shoudl deal with stats, but is currently broken

## Bugs and TODOs

This is only half-working. It was originally built on the first armory 'API' (the XSLT-in-browser version).

* The new battlenet API does not include stats (thanks Blizzard!), so that's broken. 
* Achievements don't have icons (thanks again!) and show non-faction ones (...you get the idea).
* Quests rely on scraping JS from wowhead, which will almost certainly break soon (should be updated to use <a href="https://github.com/iamcal/Wowhead-API">Wowhead-API</a>).
* It really needs some links to wowhead (quests, achievements), with nice tooltips.
* The photos gallery should be made optional
* If should include a roster, with history and recent changes
* Adding a better front page with summarized recent changes of some kind. Maybe the guild feed?
