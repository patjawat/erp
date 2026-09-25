pipeline {
    agent any

    options {
        skipDefaultCheckout(true)
        timestamps()
        disableConcurrentBuilds()
        timeout(time: 90, unit: 'MINUTES')
    }

    // push decha_dev = build :latest + deploy (รพ.ทดสอบ) เหมือนเดิม
    // กด "Build with Parameters" แล้วติ๊ก RELEASE = ปล่อยเวอร์ชันให้ รพ. อื่น (ไม่ build ใหม่)
    parameters {
        booleanParam(
            name: 'RELEASE',
            defaultValue: false,
            description: 'ปล่อยเวอร์ชัน: ติดป้าย image :latest ปัจจุบัน (ตัวที่ทดสอบแล้ว) เป็น :vX.Y.Z ตาม config/version.php และ :stable โดยไม่ build ใหม่'
        )
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

                        branches: [[name: '*/decha_dev']],

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
            when { expression { !params.RELEASE } }
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
                                --build-arg APP_BUILD=${BUILD_NUMBER} \
                                -t ${FULL_IMAGE_NAME} \
                                .
                        '''
                    }
                }
            }
        }

        stage('Push Image') {
            when { expression { !params.RELEASE } }
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
            when { expression { !params.RELEASE } }
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

        stage('Release') {
            when { expression { params.RELEASE } }
            steps {
                script {
                    docker.withRegistry(
                        'https://index.docker.io/v1/',
                        DOCKER_HUB_CREDENTIALS
                    ) {
                        sh '''
                            set -e
                            REPO=${DOCKER_HUB_USER}/${DOCKER_IMAGE}

                            VERSION=$(grep -oE "v[0-9]+\\.[0-9]+\\.[0-9]+" config/version.php | head -n 1)
                            [ -n "$VERSION" ] || { echo "อ่านเลขเวอร์ชันจาก config/version.php ไม่ได้"; exit 1; }

                            # ใช้ image :latest ตัวเดียวกับที่ รพ.ทดสอบใช้อยู่ — เลขเวอร์ชันใน image ต้องตรงกับ decha_dev
                            docker pull ${FULL_IMAGE_NAME}
                            IMG_VERSION=$(docker run --rm --entrypoint cat ${FULL_IMAGE_NAME} /app/config/version.php | grep -oE "v[0-9]+\\.[0-9]+\\.[0-9]+" | head -n 1)
                            if [ "$IMG_VERSION" != "$VERSION" ]; then
                                echo "image :latest เป็น $IMG_VERSION แต่ config/version.php เป็น $VERSION — รอ build ของ commit ที่ bump เวอร์ชันให้เสร็จก่อน"
                                exit 1
                            fi

                            # ห้ามทับเวอร์ชันที่ปล่อยไปแล้ว
                            if docker manifest inspect $REPO:$VERSION >/dev/null 2>&1; then
                                echo "มี $REPO:$VERSION บน Docker Hub แล้ว — bump config/version.php ก่อนปล่อยเวอร์ชันใหม่"
                                exit 1
                            fi

                            docker tag ${FULL_IMAGE_NAME} $REPO:$VERSION
                            docker tag ${FULL_IMAGE_NAME} $REPO:stable
                            docker push $REPO:$VERSION
                            docker push $REPO:stable

                            echo "$VERSION" > release-version.txt
                            echo "ปล่อย $VERSION เป็น stable แล้ว"
                        '''
                    }
                }
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
                    if [ -f release-version.txt ]; then
                        TELEGRAM_MESSAGE="$(printf '🚀 ปล่อยเวอร์ชัน %s เป็น stable แล้ว\nรพ. อัปเดตได้ด้วย ./update.sh\nBuild #%s\n%s' \
                            "$(cat release-version.txt)" "${BUILD_NUMBER}" "${BUILD_URL}")"
                    else
                        TELEGRAM_MESSAGE="$(printf '✅ Build #%s สำเร็จ\nJob: %s\n%s' \
                            "${BUILD_NUMBER}" "${JOB_NAME}" "${BUILD_URL}")"
                    fi

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
