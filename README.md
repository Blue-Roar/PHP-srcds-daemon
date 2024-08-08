# PHP-srcds-daemon #

A simple PHP script to monitor the status of source dedicated servers on Windows servers.

----------

## Usage ##
- Rename **config.example.php** to **config.php**
- Configure **config.php**
    - Servers to be monitored
    - Retry attempts before restarting the server
    - Path to the retry count file
    - The field from the query result as the online indicator. Should always present
- Add the script to **Task Scheduler** (taskschd.msc):
    - Action: Start a program
    - Program/script: *path-to-php.exe*
    - Arguments: *path-to-bootstrap.php*

## Notes ##
- The source dedicated servers must be run through scheduled tasks as the script use schtasks to determine whether the processes are running
- Although added, remote schedule tasks has not been tested
- Please check whether the user running the task has the permission to execute schtasks and write permission to the retry count file

Server online status query by [xPaw/PHP-Source-Query](https://github.com/xPaw/PHP-Source-Query)