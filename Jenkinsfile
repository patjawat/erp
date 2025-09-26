pipeline {
    agent any
    environment {
        DOCKER_IMAGE = "erp"
        DOCKER_TAG = "1.1"
        DOCKER_HUB_USER = "patjawat"
        DOCKER_HUB_CREDENTIALS = "erp-docker-hub"
    }

    stages {

         stage('Cleanup') {
            steps {
                deleteDir() // ลบ workspace เก่า ก่อน checkout
            }
        }

        stage('Checkout') {
            steps {
                git branch: 'main', url: 'https://github.com/patjawat/erp.git'
            }
        }
        
        stage('Build Image') {
            steps {
                script {
                    // สร้าง Docker image
                    docker.build("${DOCKER_HUB_USER}/${DOCKER_IMAGE}:${DOCKER_TAG}")
                }
            }
        }

        stage('Push Image') {
            steps {
                script {
                    // login และ push ไป Docker Hub
                    docker.withRegistry('https://index.docker.io/v1/', DOCKER_HUB_CREDENTIALS) {
                        def app = docker.image("${DOCKER_HUB_USER}/${DOCKER_IMAGE}:${DOCKER_TAG}")
                        app.push()
                    }
                }
            }
        }
    }
}
