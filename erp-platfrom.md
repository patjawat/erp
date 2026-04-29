# hospital-platform
│
├── docker
│   ├── nginx
│   │   └── default.conf
│   │
│   ├── keycloak
│   │
│   └── mysql
│
├── services
│
│   ├── yii2-erp
│   │
│   ├── nest-api
│   │
│   ├── react-app
│   │
│   ├── angular-app
│   │
│   ├── vue-app
│   │
│   └── nextjs-app
│
├── uploads
│
├── docker-compose.yml
│
└── .env

# Reverse Proxy (Nginx)

/          -> Yii2 ERP
/react     -> React
/app1      -> Angular
/vuejs     -> Vue
/nextjs    -> NextJS
/api       -> NestJS
/auth      -> Keycloak


# SSO Login Flow
User
 |
 v
Frontend
 |
 v
Keycloak Login
 |
 v
JWT Token
 |
 v
NestJS API
 |
 v
Database

# Infographic Architecture

                +-------------------+
                |      USERS        |
                +---------+---------+
                          |
                          v
                +-------------------+
                |      NGINX        |
                |   Reverse Proxy   |
                +---------+---------+
                          |
     +--------------------+--------------------+
     |                    |                    |
     v                    v                    v

+---------+       +--------------+      +---------------+
|  Yii2   |       |   Frontend   |      |   Keycloak    |
|  ERP    |       | React/VueJS  |      |     SSO       |
+----+----+       +------+-------+      +-------+-------+
     |                   |                      |
     +----------+--------+                      |
                |                               |
                v                               v
           +-----------------------+
           |      NestJS API       |
           |   Business Logic      |
           +-----------+-----------+
                       |
                       v
      +----------------+----------------+
      |                                 |
      v                                 v
 +-----------+                     +-----------+
 |   MySQL   |                     |   Redis   |
 | Database  |                     |  Cache    |
 +-----------+                     +-----------+
      |
      v
 +-----------+
 |   MinIO   |
 | FileStore |
 +-----------+