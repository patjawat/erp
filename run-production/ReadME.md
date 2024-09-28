1 Run Build 
    => docker build -t erp:v1 .
    => docker build --platform linux/amd64  -t erp:v1 .
2. Save Image 
    => docker save -o erpv1.tar erp:v1

3. ย้ายไปใช้งาน
    => scp erpv1.tar ssh root@119.59.124.113:/home/erp-production/ 

4. load Image ไปใช้งาน
    => docker load -i  erpv1.tar 