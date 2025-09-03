pipeline {
    agent any
    environment {
        DOCKER_IMAGE = "erp"
        DOCKER_TAG = "1.1"
        DOCKER_HUB_USER = "patjawat"
        DOCKER_HUB_CREDENTIALS = "erp-docker-hub"
    }

    stages {
        stage('Build & Push') {
            steps {
                script {
                    // build image
                    def app = docker.build("${DOCKER_HUB_USER}/${DOCKER_IMAGE}:${DOCKER_TAG}")

                    // login & push ใช้ credentials ที่สร้างไว้
                    docker.withRegistry('https://index.docker.io/v1/', DOCKER_HUB_CREDENTIALS) {
                        app.push()
                    }
                }
            }
        }
    }
}
