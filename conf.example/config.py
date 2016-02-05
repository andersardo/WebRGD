#location dependent configuration
import socket

#which WSGI-server to use
#The full list is available through server_names.
#wsgiserver ='wsgiref'
wsgiserver = 'cherrypy'

#Which IP to listen to
host = socket.gethostname()

#Which port to use
port = 8085

#which mailserver to use
mailserver = 'localhost'

#enable/disable wsgiserver logging and saving actions in originaldata
logging = True
