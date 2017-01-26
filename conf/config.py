#location dependent configuration
import socket

#which WSGI-server to use
#The full list is available through server_names.
#wsgiserver ='wsgiref'
wsgiserver = 'cherrypy'

#Which IP to listen to
#host = socket.gethostname() 
#host = 'rgd.dis.se'
host = 'localhost'

#Which port to use
port = 8084

#which mailserver to use
mailserver = 'mail.dis.se'

#enable/disable wsgiserver logging and saving actions in originaldata
logging = True
