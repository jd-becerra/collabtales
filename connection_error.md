If you fail to start a port because it's used, kill the application.

netstat -ano | findstr :{PORT}   (where port is a number)
taskill /PID {PID} /F   (where PID is the process ID we got from netstat)