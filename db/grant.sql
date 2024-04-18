GRANT All PRIVILEGES ON *.*  to 'root'@'localhost' IDENTIFIED BY 'docker'  WITH GRANT OPTION;
GRANT All PRIVILEGES ON *.*  to 'root'@'127.0.0.1' IDENTIFIED BY 'docker'  WITH GRANT OPTION;
GRANT All PRIVILEGES ON *.*  to 'root'@'::1' IDENTIFIED BY 'docker'  WITH GRANT OPTION;
GRANT All PRIVILEGES ON *.*  to 'root'@'%' IDENTIFIED BY 'docker'  WITH GRANT OPTION;
flush privileges;