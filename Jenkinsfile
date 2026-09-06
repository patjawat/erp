pipeline {
    agent any

    options {
        skipDefaultCheckout(true)
        timestamps()
        disableConcurrentBuilds()
        timeout(time: 90, unit: 'MINUTES')
    }

    environment {
        DOCKER_IMAGE = 'erp'
        DOCKER_TAG = 'latest'
        DOCKER_HUB_USER = 'patjawat'
        DOCKER_HUB_CREDENTIALS = 'erp-docker-hub'

        FULL_IMAGE_NAME = "${DOCKER_HUB_USER}/${DOCKER_IMAGE}:${DOCKER_TAG}"

        DEPLOY_PATH = "/home/cpherp/web-server"
    }

    stages {

        stage('Cleanup Workspace') {
            steps {
                deleteDir()
            }
        }

        stage('Checkout') {
            steps {
                retry(3) {
                    checkout([
                        $class: 'GitSCM',

                        branches: [[name: '*/ç']],

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

                sh 'git log -1 --oneline'
            }
        }

        stage('Build Image') {
            steps {
                script {
                    // ดึง image เดิมจาก registry มาเป็นแหล่ง cache ก่อน build
                    // (workspace ถูก deleteDir ทุกครั้ง แต่ layer cache อยู่ที่ Docker daemon
                    //  เมื่อรวมกับ inline cache ทำให้ build ครั้งถัดไป reuse layer composer ได้)
                    docker.withRegistry(
                        'https://index.docker.io/v1/',
                        DOCKER_HUB_CREDENTIALS
                    ) {
                        sh '''
                            set -e
                            export DOCKER_BUILDKIT=1
                            export BUILDKIT_PROGRESS=plain

                            docker pull ${FULL_IMAGE_NAME} || true

                            docker build \
                                --progress=plain \
                                --cache-from ${FULL_IMAGE_NAME} \
                                --build-arg BUILDKIT_INLINE_CACHE=1 \
                                -t ${FULL_IMAGE_NAME} \
                                .
                        '''
                    }
                }
            }
        }

        stage('Push Image') {
            steps {
                script {
                    docker.withRegistry(
                        'https://index.docker.io/v1/',
                        DOCKER_HUB_CREDENTIALS
                    ) {
                        docker.image(FULL_IMAGE_NAME).push()
                    }
                }
            }
        }

        stage('Deploy') {
            steps {

                sh '''
                    set -e

                    echo "===================================="
                    echo "Deploy ERP"
                    echo "===================================="

                    docker compose \
                        --project-directory ${DEPLOY_PATH} \
                        -f ${DEPLOY_PATH}/docker-compose.yml \
                        pull

                    docker compose \
                        --project-directory ${DEPLOY_PATH} \
                        -f ${DEPLOY_PATH}/docker-compose.yml \
                        up -d --remove-orphans

                    chmod +x ${DEPLOY_PATH}/script/migrate.sh

                    ${DEPLOY_PATH}/script/migrate.sh

                    docker compose \
                        --project-directory ${DEPLOY_PATH} \
                        -f ${DEPLOY_PATH}/docker-compose.yml \
                        ps
                '''
            }
        }

    }

    post {

        success {
            echo "✅ Deploy Success"

            withCredentials([
                string(
                    credentialsId: 'telegram-bot-token',
                    variable: 'TELEGRAM_BOT_TOKEN'
                ),
                string(
                    credentialsId: 'telegram-chat-id',
                    variable: 'TELEGRAM_CHAT_ID'
                )
            ]) {
                sh '''
                    TELEGRAM_MESSAGE="$(printf '✅ Build #%s สำเร็จ\nJob: %s\n%s' \
                        "${BUILD_NUMBER}" "${JOB_NAME}" "${BUILD_URL}")"

                    curl -s -X POST \
                        "https://api.telegram.org/bot${TELEGRAM_BOT_TOKEN}/sendMessage" \
                        -d chat_id="${TELEGRAM_CHAT_ID}" \
                        --data-urlencode text="${TELEGRAM_MESSAGE}"
                '''
            }
        }

        failure {
            echo "❌ Deploy Failed"

            withCredentials([
                string(
                    credentialsId: 'telegram-bot-token',
                    variable: 'TELEGRAM_BOT_TOKEN'
                ),
                string(
                    credentialsId: 'telegram-chat-id',
                    variable: 'TELEGRAM_CHAT_ID'
                )
            ]) {
                sh '''
                    TELEGRAM_MESSAGE="$(printf '❌ Build #%s ล้มเหลว\nJob: %s\n%s' \
                        "${BUILD_NUMBER}" "${JOB_NAME}" "${BUILD_URL}")"

                    curl -s -X POST \
                        "https://api.telegram.org/bot${TELEGRAM_BOT_TOKEN}/sendMessage" \
                        -d chat_id="${TELEGRAM_CHAT_ID}" \
                        --data-urlencode text="${TELEGRAM_MESSAGE}"
                '''
            }
        }

        always {

            deleteDir()

            sh '''
                docker image prune -f || true
            '''
        }
    }
}
