#Created 2016-02-05 by Anders Ardo
FROM debian:jessie
MAINTAINER Anders Ardo (anders.ardo@gmail.com)

ENV DEBIAN_FRONTEND noninteractive

# MongoDB 3.4
RUN apt-key adv --keyserver hkp://keyserver.ubuntu.com:80 --recv 0C49F3730359A14518585931BC711F9BA15703C6
RUN echo "deb http://repo.mongodb.org/apt/debian jessie/mongodb-org/3.4 main" | tee /etc/apt/sources.list.d/mongodb-org-3.4.list
# For java openjdk-8
RUN echo "deb http://debian.lth.se/debian jessie-backports main contrib non-free" | tee /etc/apt/sources.list.d/jessie-backports.list

# Sätt MySQL-root-lösenordet till "rgd".
#setup
RUN echo "mysql-server-5.5 mysql-server/root_password password rgd" | debconf-set-selections
RUN echo "mysql-server-5.5 mysql-server/root_password_again password rgd" | debconf-set-selections

# Några standardpaket
RUN apt-get update && apt-get install -y \
        locales supervisor libsvm-dev python-libsvm openssl \
        python-setuptools python-dev build-essential gcc g++ make wget graphviz \
	mongodb-org mysql-server mysql-client php5-cli php5-mysql \
	openjdk-8-jdk-headless openjdk-8-jdk \
        openjdk-8-jre-headless openjdk-8-jre \
        ca-certificates-java=20161107~bpo8+1 ant \
        && wget https://bootstrap.pypa.io/get-pip.py \
	&& python get-pip.py \
        && pip install pymongo bottle Beaker cheroot bottle-cork openpyxl \
        && apt-get purge -y python-setuptools wget \
        && apt-get autoremove -y \
        && apt-get clean \
        && rm -rf /var/lib/apt/lists/*

RUN dpkg-reconfigure locales && \
    locale-gen C.UTF-8 && \
    /usr/sbin/update-locale LANG=C.UTF-8
RUN echo 'en_US.UTF-8 UTF-8' >> /etc/locale.gen &&  locale-gen
# Set the locale
ENV LANG en_US.UTF-8  
ENV LANGUAGE en_US:en  
ENV LC_ALL en_US.UTF-8

#pylucene
COPY pylucene-6.5.0-src.tar.gz ./pylucene-6.5.0-src.tar.gz
#unpack
RUN tar zxf ./pylucene-6.5.0-src.tar.gz
#fix makefile
COPY Makefile.pylucene ./pylucene-6.5.0/Makefile
#jcc
WORKDIR /pylucene-6.5.0/jcc
ENV JCC_JDK /usr/lib/jvm/java-8-openjdk-amd64
RUN python setup.py build && python setup.py install
#pylucene
WORKDIR /pylucene-6.5.0
RUN make
RUN make install

WORKDIR /
# Skapa MySQL databas som används av PHP-programmet för 
# indatavalidering.
COPY RGDindatavalid.sql ./RGDindatavalid.sql
COPY SQLsetup.sql ./SQLsetup.sql
RUN service mysql start && mysql --user=root --password=rgd < SQLsetup.sql

#version of svmutils 3.12-1 without annoying print
COPY svmutil.py /usr/share/pyshared/svmutil.py

#starts everything
COPY supervisorRGD.conf /etc/supervisor/conf.d/openRGD.conf

EXPOSE 8085
ENTRYPOINT /bin/bash

