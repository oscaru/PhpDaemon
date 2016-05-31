# PhpDaemon

Richard Stevenson describes the following steps for writing daemons:

    1- Resetting the file mode creation mask to 0 function umask(), to mask some bits of access rights from the starting process.
    2- Cause fork() and finish the parent process. This is done so that if the process was launched as a group, the shell believes that the group finished at the same time, the child inherits the process group ID of the parent and gets its own process ID. This ensures that it will not become process group leader.
    3- Create a new session by calling setsid(). The process becomes a leader of the new session, the leader of a new group of processes and loses the control of the terminal.
    4- Make the root directory of the current working directory as the current directory will be mounted.
    5- Close all file descriptors.
    6- Make redirect descriptors 0,1 and 2 (STDIN, STDOUT and STDERR) to /dev/null or files /var/log/project_name.out because some standard library functions use these descriptors.
    7- Record the pid (process ID number) in the pid-file: /var/run/projectname.pid.
    8- Correctly process the signals and SigTerm SigHup: end with the destruction of all child processes and pid - files and / or re-configuration.


Return Status

0 : Success - we’ve exited normally.
1 : General Error - usually used for application/language specific errors and syntax errors
2 : Incorrect Usage
126 : Command is not executable - usually permissions related
127 : Command Not Found
128+N (up to 165): Command terminated by POSIX Signal number N - e.g. In the case of

kill -9 myscript.php it should return code 137 (128+9)
130 : Command terminated by Ctrl-C (Ctrl-C is POSIX code 2)

