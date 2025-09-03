pipeline {
    agent any

    environment {
        DOCKER_HUB_USER = "patjawat"
        DOCKER_IMAGE = "erp"
        DOCKER_TAG = "1.1"
    }

    stages {
        stage('Checkout') {
            steps {
                git branch: 'main', url: 'https://github.com/patjawat/erp.git'
            }
        }

        stage('Build Image') {
            steps {
                sh """
                docker buildx build --platform linux/amd64 -t ${DOCKER_IMAGE}:${DOCKER_TAG} . --load
                """
            }
        }

        stage('Tag Image') {
            steps {
                sh """
                docker tag ${DOCKER_IMAGE}:${DOCKER_TAG} ${DOCKER_HUB_USER}/${DOCKER_IMAGE}:${DOCKER_TAG}
                """
            }
        }

        stage('Docker Login') {
            steps {
                echo "login docker hub"
            }
        }

        stage('Push to Docker Hub') {
            steps {

                echo "push image" 
            }
        }

        stage('Clean Up') {
            steps {
                echo "clean"
            }
        }
    }
}
