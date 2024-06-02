# delete vendor folder in directory
find . -name "vendor" -type d -prune -exec rm -rf '{}' +

tar --exclude='dansai-hospital-erp.org/vendor' -cvf /home/backups/test2.tar.gz dansai-hospital-erp.org