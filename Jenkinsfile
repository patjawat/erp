pipeline {
    agent any

    options {
        // ป้องกัน Jenkins checkout อัตโนมัติซ้ำ
        skipDefaultCheckout(true)

        // เพิ่ม timestamp ใน Console Output
        timestamps()

        // ป้องกัน Build เดียวกันทำงานซ้อนกัน
        disableConcurrentBuilds()

        // จำกัดเวลารวมของ Pipeline
        timeout(time: 90, unit: 'MINUTES')
    }

    environment {
        DOCKER_IMAGE = 'erp'
        DOCKER_TAG = 'latest'
        DOCKER_HUB_USER = 'patjawat'
        DOCKER_HUB_CREDENTIALS = 'erp-docker-hub'

        // ชื่อ image แบบเต็ม
        FULL_IMAGE_NAME = "${DOCKER_HUB_USER}/${DOCKER_IMAGE}:${DOCKER_TAG}"
    }

    stages {

        stage('Cleanup Workspace') {
            steps {
                echo '🧹 Cleaning old workspace...'
                deleteDir()
            }
        }

        stage('Checkout') {
            steps {
                echo '📥 Cloning source code...'

                retry(3) {
                    checkout([
                        $class: 'GitSCM',

                        branches: [[
                            name: '*/main'
                        ]],

                        userRemoteConfigs: [[
                            url: 'https://github.com/patjawat/erp.git'
                        ]],

                        extensions: [
                            [
                                $class: 'CloneOption',
                                shallow: true,
                                depth: 1,
                                noTags: true,
                                honorRefspec: true,
                                timeout: 60
                            ],
                            [
                                $class: 'CheckoutOption',
                                timeout: 60
                            ]
                        ]
                    ])
                }

                sh '''
                    echo "Current commit:"
                    git log -1 --oneline
                '''
            }
        }

        stage('Build Image') {
            steps {
                echo "🐳 Building Docker image: ${FULL_IMAGE_NAME}"

                script {
                    docker.build(
                        "${FULL_IMAGE_NAME}",
                        '--pull .'
                    )
                }
            }
        }

        stage('Push Image') {
            steps {
                echo "📤 Pushing Docker image: ${FULL_IMAGE_NAME}"

                script {
                    docker.withRegistry(
                        'https://index.docker.io/v1/',
                        DOCKER_HUB_CREDENTIALS
                    ) {
                        docker.image("${FULL_IMAGE_NAME}").push()
                    }
                }
            }
        }
    }

    post {
        success {
            echo '✅ Build and push completed successfully'
        }

        failure {
            echo '❌ Pipeline failed'
        }

        aborted {
            echo '⚠️ Pipeline was aborted'
        }

        always {
            echo '🧹 Cleaning Jenkins workspace...'

            script {
                // ลบ workspace แต่ไม่ลบ Docker image และ build cache ทั้งเครื่อง
                deleteDir()

                // ล้างเฉพาะ container ที่หยุดแล้วและ image ชั่วคราว
                timeout(time: 5, unit: 'MINUTES') {
                    sh '''
                        docker container prune -f || true
                        docker image prune -f || true
                    '''
                }
            }
        }
    }
}