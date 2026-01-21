# AIOS Cleanup

[All In One Security & Firewall (AIOS)](https://en-gb.wordpress.org/plugins/all-in-one-wp-security-and-firewall/) is a great WordPress plugin for securing your site, but it has a nasty habit of filling up the database with log entries. Of course, these are useful, but being able to purge them after a certain length of time ought to be an option.

This rectifies that by deleteing all entries in the table over seven days old.

## Installation

The plugin is just one file, so you can put this in your `plugins` or `mu-plugins` folder as desired - feel free to tweak the length of time, it is stored in `$period`. The plugin only runs if the user is an admin - change `manage_options` to another capability to alter this.

Should you wish to install it using Composer, add this to the `repositories` secion of composer.json
```
{
    "type": "package",
    "package": {
        "name": "adam-ainsworth/aios-cleanup",
        "version": "1.0.0",
        "type": "wordpress-plugin",
        "source": {
            "url": "https://github.com/adam-ainsworth/aios-cleanup.git",
            "type": "git",
            "reference": "production"
        }
    }
}
```

And then add it to the `require` section

```
"adam-ainsworth/aios-cleanup": "*"
```

And then run `composer update`.

## Usage

Click the *Cleanup!* link under the plugin name in the plugin listing page. You should get an alert to confirm success or failure. Success will tell you the number of rows that have been deleted.

If there are a lot of rows, the AJAX call may time out. **Do not** click the link again - the query will still be running but you will have to check when it is finished manually (check the number of rows in the table periodically, or see how much CPU the SQL process is using).

## Bugs, improvements etc

To be honest, this is a quick and dirty hack. It should work on most instances but please let me know if you run in to any trouble by filing an issue. If there is a compelling reason to add extra features or make it user friendly, I may do so but anyone is free to take this code and embellish it as they wish, subject to the license.

I haven't submitted this to the official WP plugin repository because I very much doubt they will allow it.

**Usage of this plugin is entirely at your own risk and I do not accept any responsibility for downtime, data loss or any other kind of problem if you run it without completely understanding what it does. Always back up your data before running risky queries on your DB!**
