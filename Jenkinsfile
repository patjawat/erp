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
                deleteDir() // ลบ workspace เก่าก่อน checkout
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

        post {
            always {
                echo "🧹 Cleaning up Docker build cache..."
                script {
                    // ใช้ timeout เพื่อป้องกันขั้นตอนค้าง
                    timeout(time: 1, unit: 'MINUTES') {
                        sh 'docker system prune -af || true'
                    }
                }
            }
        }
}
