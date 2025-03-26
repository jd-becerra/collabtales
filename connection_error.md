If you fail to start a port because it's used, kill the application.

netstat -ano | findstr :{PORT}   (where port is a number), get PID and:
taskkill /PID {PID} /F   (where PID is the process ID we got from netstat. use admin privileges)


# Composer
Este proyecto necesita composer para poder mandar correos
https://github.com/PHPMailer/PHPMailer