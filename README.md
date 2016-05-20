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

