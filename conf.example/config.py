#location dependent configuration
import socket

#which WSGI-server and protocol to use
import bottle
#The full list of bottle available servers can be found through server_names.
#wsgiserver ='wsgiref'
#Default use cheroot WSGI-server
from cheroot import wsgi

SSL = False #HTTP
#SSL = True  #uncomment if you want to use SSL and HTTPS
if SSL:   #HTTPS
    from cheroot.ssl.builtin import BuiltinSSLAdapter
    import ssl
    # Create our own sub-class of Bottle's ServerAdapter
    # so that we can specify SSL. Using just server='cherrypy'
    # uses the default cherrypy server, which doesn't use SSL
    class SSLCherryPyServer(bottle.ServerAdapter):
        def run(self, handler):
            server = wsgi.Server((self.host, self.port), handler)
            #Configure your certificate files
            server.ssl_adapter = BuiltinSSLAdapter('cacert.pem', 'privkey.pem')

            # By default, the server will allow negotiations with extremely old protocols
            # that are susceptible to attacks, so we only allow TLSv1.2
            server.ssl_adapter.context.options |= ssl.OP_NO_TLSv1
            server.ssl_adapter.context.options |= ssl.OP_NO_TLSv1_1
            try:
                server.start()
            finally:
                server.stop()
    wsgiserver = SSLCherryPyServer
else:  #HTTP
    # Create our own sub-class of Bottle's ServerAdapter
    class CherryPyServer(bottle.ServerAdapter):
        def run(self, handler):
            server = wsgi.Server((self.host, self.port), handler)
            try:
                server.start()
            finally:
                server.stop()
    wsgiserver = CherryPyServer

#Which IP to listen to
#host = 'localhost'   #localhost
host = socket.gethostname() #or your IP-no

#Which port to use
port = 8085

#which mailserver to use
mailserver = 'localhost'

#enable/disable wsgiserver logging and saving actions in originaldata
logging = True
