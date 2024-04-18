

# Backup storage directory 
backupfolder=$(pwd)
app_name=dansai

# Notification email address 
recipient_email=username@mail.com

# MySQL user
user=root

# MySQL password
password=docker
#database Name
database_name=dansai

# Number of days to store the backup 
keep_day=30 
dir_name=$(date +%d-%m-%Y_%H-%M-%S);
mkdir $(pwd)/$dir_name

sqlfile=$dir_name/$app_name-DB-$(date +%d-%m-%Y_%H-%M-%S).sql
zipfile=$backupfolder/all-database-$(date +%d-%m-%Y_%H-%M-%S).zip 

# Create a backup 
docker exec dansai_db /usr/bin/mysqldump -u root --password=$password $database_name  > $sqlfile 
cp -r modules/filemanager/fileupload $dir_name

# if [ $? == 0 ]; then
#   echo 'Sql dump created' 
# else
#   echo 'mysqldump return non-zero code' | mailx -s 'No backup was created!' $recipient_email  
#   exit 
# fi 

# # Compress backup 
# zip $zipfile $sqlfile 
# if [ $? == 0 ]; then
#   echo 'The backup was successfully compressed' 
# else
#   echo 'Error compressing backup' | mailx -s 'Backup was not created!' $recipient_email 
#   exit 
# fi 
# rm $sqlfile 
# echo $zipfile | mailx -s 'Backup was successfully created' $recipient_email 

# # Delete old backups 
# find $backupfolder -mtime +$keep_day -delete

# docker exec erp2-mysqlDB-1 /usr/bin/mysqldump -u root --password=docker "${DB_NAME}" > backup.sql
#!/bin/bash
####################################
#
# Backup to NFS mount script.
#
####################################
    
# What to backup. 
# backup_files="/home /var/spool/mail /etc /root /boot /opt"
    
# Where to backup to.
# dest="/mnt/backup"
    
# Create archive filename.
# docker exec erp2-mysqlDB-1 /usr/bin/mysqldump -u root --password=docker "${DB_NAME}" > $(pwd)/$dir_name/backup.sql