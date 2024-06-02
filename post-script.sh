$ cat post-script.sh
##!/bin/bash

# #### Example Post Script
$1=EXIT_CODE (After running backup routine)
$2=DBXX_TYPE (Type of Backup)
$3=DBXX_HOST (Backup Host)
#4=DBXX_NAME (Name of Database backed up
$5=BACKUP START TIME (Seconds since Epoch)
$6=BACKUP FINISH TIME (Seconds since Epoch)
$7=BACKUP TOTAL TIME (Seconds between Start and Finish)
$8=BACKUP FILENAME (Filename)
$9=BACKUP FILESIZE
$10=HASH (If CHECKSUM enabled)
$11=MOVE_EXIT_CODE

echo "${1} ${2} Backup Completed on ${3} for ${4} on ${5} ending ${6} for a duration of ${7} seconds. Filename: ${8} Size: ${9} bytes MD5: ${10}"


# cat post-script.sh
# ##!/bin/bash

tar -czvf /backup/"$(date '+%Y-%m-%d %H:%M:%S').tar.gz" /home/app/modules/filemanager/fileupload
echo "success"