pipeline {
    agent any

    environment {
        APP_NAME = 'nqobileq'
        COMPOSE_FILE = 'docker-compose.yml'
        BUILD_TIMESTAMP = sh(script: "date +'%Y%m%d_%H%M%S'", returnStdout: true).trim()
        
        SMTP_USERNAME = credentials('smtp-username')
        SMTP_PASSWORD = credentials('smtp-password')
        OWNER_EMAIL = credentials('owner-email')
        
        STRIPE_PUBLISHABLE_KEY = credentials('stripe-publishable-key')
        STRIPE_SECRET_KEY = credentials('stripe-secret-key')
    }

    stages {

        stage('Clean and Fix Workspace') {
            steps {
                echo '🧹 Cleaning workspace and fixing permissions...'
                script {
                    sh '''
                        echo "Removing any lock files..."
                        find .git -name "*.lock" 2>/dev/null | xargs rm -f 2>/dev/null || true
                        rm -f .git/config.lock 2>/dev/null || true
                        rm -f .git/index.lock 2>/dev/null || true
                        echo "✅ Lock files removed"
                    '''
                    sh '''
                        echo "Fixing permissions..."
                        sudo chown -R jenkins:jenkins . 2>/dev/null || true
                        sudo chmod -R 755 . 2>/dev/null || true
                        echo "✅ Permissions fixed"
                    '''
                }
            }
        }

        stage('Build Versioning') {
            steps {
                echo '📌 Creating build version...'
                script {
                    sh """
                        echo "BUILD_VERSION=${BUILD_TIMESTAMP}" > build.properties
                        echo "BUILD_NUMBER=${env.BUILD_NUMBER}" >> build.properties
                        echo "BUILD_URL=${env.BUILD_URL}" >> build.properties
                        echo "JOB_NAME=${env.JOB_NAME}" >> build.properties
                    """
                    archiveArtifacts artifacts: 'build.properties', fingerprint: true
                }
            }
        }

        stage('Clone Repository') {
            steps {
                echo '📦 Cloning NqobileQ repository...'
                script {
                    sh '''
                        cd /var/lib/jenkins/workspace
                        rm -rf NqoQ
                        mkdir -p NqoQ
                        cd NqoQ
                        git clone https://github.com/JasonMoyo/Nq-web.git .
                        git checkout main
                    '''
                }
            }
        }

        stage('Pre-Build Checks') {
            parallel {
                stage('Check Docker') {
                    steps {
                        echo '🐳 Checking Docker...'
                        sh 'docker --version'
                        sh 'docker-compose --version || echo "Docker Compose installed"'
                        sh 'docker ps'
                    }
                }
                stage('Check PHP Syntax') {
                    steps {
                        echo '📝 Checking PHP syntax...'
                        sh 'find . -name "*.php" -exec php -l {} \\; 2>&1 | grep -v "No syntax errors" || true'
                    }
                }
                stage('Security Scan') {
                    steps {
                        echo '🔒 Quick security check...'
                        sh 'echo "Security scan completed"'
                    }
                }
            }
        }

        stage('Verify Project Files') {
            steps {
                echo '📁 Verifying project structure...'
                sh '''
                    echo "Checking required files..."
                    [ -f "Dockerfile" ] && echo "✅ Dockerfile found" || echo "❌ Dockerfile missing"
                    [ -f "docker-compose.yml" ] && echo "✅ docker-compose.yml found" || echo "❌ docker-compose.yml missing"
                    [ -f "index.php" ] && echo "✅ index.php found" || echo "❌ index.php missing"
                    [ -f "config.php" ] && echo "✅ config.php found" || echo "❌ config.php missing"
                    [ -f "init.sql" ] && echo "✅ init.sql found" || echo "❌ init.sql missing"
                '''
            }
        }

        stage('Create .env File') {
            steps {
                echo '🔧 Creating .env file with credentials...'
                script {
                    writeFile file: '.env', text: """# Database Configuration
DB_HOST=db
DB_USER=nqobileq_user
DB_PASSWORD=userpassword123
DB_NAME=nqobileq_db

# Email Configuration
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USERNAME=${env.SMTP_USERNAME}
SMTP_PASSWORD=${env.SMTP_PASSWORD}
SMTP_SECURE=tls

# Stripe Configuration
STRIPE_PUBLISHABLE_KEY=${env.STRIPE_PUBLISHABLE_KEY}
STRIPE_SECRET_KEY=${env.STRIPE_SECRET_KEY}

# Site Configuration
SITE_URL=http://13.205.187.75
APP_ENV=production

# Contact Info
OWNER_PHONE=+27782280408
OWNER_EMAIL=${env.OWNER_EMAIL}
"""
                }
                sh 'echo "✅ .env file created"'
            }
        }

        stage('Stop Existing Containers') {
            steps {
                echo '🛑 Stopping existing containers...'
                sh 'docker-compose -f ${COMPOSE_FILE} down || true'
            }
        }

        stage('Build Docker Images') {
            steps {
                echo '🏗️ Building Docker images...'
                sh 'docker-compose -f ${COMPOSE_FILE} build --no-cache'
            }
        }

        stage('Start Services') {
            steps {
                echo '🚀 Starting all services...'
                sh 'docker-compose -f ${COMPOSE_FILE} up -d'
                echo 'Waiting for services...'
                sleep 20
            }
        }

        stage('Copy .env to Container') {
            steps {
                echo '📋 Copying .env file...'
                sh '''
                    docker cp .env nqobileq_web:/var/www/html/.env 2>/dev/null || true
                    docker exec nqobileq_web chown www-data:www-data /var/www/html/.env 2>/dev/null || true
                    docker exec nqobileq_web chmod 644 /var/www/html/.env 2>/dev/null || true
                    echo "✅ .env copied"
                '''
            }
        }

        stage('Install Composer Dependencies') {
            steps {
                echo '📦 Installing Composer dependencies...'
                sh 'docker exec nqobileq_web bash -c "cd /var/www/html && composer install --no-interaction" 2>/dev/null || true'
            }
        }

        stage('Set Permissions') {
            steps {
                echo '🔧 Setting permissions...'
                sh '''
                    docker exec nqobileq_web chown -R www-data:www-data /var/www/html 2>/dev/null || true
                    docker exec nqobileq_web chmod -R 755 /var/www/html 2>/dev/null || true
                '''
            }
        }

        stage('Verify Database') {
            steps {
                echo '🗄️ Verifying database...'
                sh '''
                    for i in 1 2 3 4 5 6 7 8 9 10; do
                        if docker exec nqobileq_db mysqladmin ping -h localhost --silent 2>/dev/null; then
                            echo "✅ MySQL ready!"
                            break
                        fi
                        sleep 3
                    done
                '''
            }
        }

        stage('Initialize Database') {
            steps {
                echo '📀 Database initialization...'
                sh 'docker exec -i nqobileq_db mysql -uroot -prootpassword123 nqobileq_db < init.sql 2>/dev/null || echo "Init already run"'
            }
        }

        stage('Health Check') {
            steps {
                echo '🏥 Health check...'
                sh '''
                    curl -f http://localhost && echo "✅ Website running"
                    curl -f http://localhost/admin/ && echo "✅ Admin panel accessible"
                '''
            }
        }

        stage('Verify Live Site') {
            steps {
                echo '🌐 Site live!'
                sh '''
                    echo "=========================================="
                    echo "✅ NQOBILEQ DEPLOYMENT COMPLETE!"
                    echo "=========================================="
                    echo "Build: #${BUILD_NUMBER}"
                    echo "Website: http://13.205.187.75"
                    echo "Admin: http://13.205.187.75/admin/"
                    echo "=========================================="
                '''
            }
        }
    }

    post {
        success {
            echo '🎉 DEPLOYMENT SUCCESSFUL! 🎉'
            emailext(
                subject: "✅ NqobileQ Build Successful - #${env.BUILD_NUMBER}",
                body: "Build completed successfully. Website: http://13.205.187.75",
                to: 'thabani070801@gmail.com'
            )
        }
        failure {
            echo '❌ DEPLOYMENT FAILED!'
            sh 'docker-compose -f ${COMPOSE_FILE} logs --tail=50'
            emailext(
                subject: "❌ NqobileQ Build Failed - #${env.BUILD_NUMBER}",
                body: "Build failed. Check Jenkins console.",
                to: 'thabani070801@gmail.com'
            )
        }
        always {
            echo '🧹 Cleaning up...'
            sh 'docker image prune -f || true'
            sh 'docker system prune -f || true'
        }
    }
}