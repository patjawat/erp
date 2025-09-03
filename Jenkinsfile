pipeline {
    agent any
    environment {
        DOCKER_IMAGE = "erp"
        DOCKER_TAG = "1.1"
        DOCKER_HUB_CREDENTIALS = "erp-docker-hub"
    }
    stages {
        stage('Build & Push') {
            steps {
                script {
                    def app = docker.build("${DOCKER_IMAGE}:${DOCKER_TAG}")
                    docker.withRegistry('https://index.docker.io/v1/', DOCKER_HUB_CREDENTIALS) {
                        app.push()
                    }
                }
            }
        }
    }
}
