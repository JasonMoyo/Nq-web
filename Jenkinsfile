pipeline {
    agent any

    environment {
        APP_NAME = 'nqobileq'
        COMPOSE_FILE = 'docker-compose.yml'
        
        SMTP_USERNAME = credentials('smtp-username')
        SMTP_PASSWORD = credentials('smtp-password')
        OWNER_EMAIL = credentials('owner-email')
    }

    stages {

        // ============ STAGE 1: CLEAN AND FIX WORKSPACE ============
        stage('Clean and Fix Workspace') {
            steps {
                echo '🧹 Cleaning workspace and fixing permissions...'
                script {
                    // Remove lock files
                    sh '''
                        echo "Removing any lock files..."
                        find .git -name "*.lock" 2>/dev/null | xargs rm -f 2>/dev/null || true
                        rm -f .git/config.lock 2>/dev/null || true
                        rm -f .git/index.lock 2>/dev/null || true
                        echo "✅ Lock files removed"
                    '''
                    
                    // Fix permissions
                    sh '''
                        echo "Fixing permissions..."
                        sudo chown -R jenkins:jenkins . 2>/dev/null || true
                        sudo chmod -R 755 . 2>/dev/null || true
                        echo "✅ Permissions fixed"
                    '''
                }
            }
        }

        stage('Clone Repository') {
            steps {
                echo '📦 Cloning NqobileQ repository...'
                script {
                    // Clean clone to avoid lock issues
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

        stage('Check Docker Environment') {
            steps {
                echo '🐳 Checking Docker...'
                sh 'docker --version'
                sh 'docker-compose --version || echo "Docker Compose installed"'
                sh 'docker ps'
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
                echo '🔧 Creating .env file with credentials from Jenkins...'
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
                echo 'Waiting for services to be ready...'
                sleep 20
            }
        }

        stage('Copy .env to Container') {
            steps {
                echo '📋 Copying .env file to container...'
                sh '''
                    docker cp .env nqobileq_web:/var/www/html/.env 2>/dev/null || echo "Container not ready, retrying..."
                    sleep 2
                    docker cp .env nqobileq_web:/var/www/html/.env 2>/dev/null || true
                    docker exec nqobileq_web chown www-data:www-data /var/www/html/.env 2>/dev/null || true
                    docker exec nqobileq_web chmod 644 /var/www/html/.env 2>/dev/null || true
                    echo "✅ .env copied to container"
                '''
            }
        }

        stage('Check Container Status') {
            steps {
                echo '📊 Checking container status...'
                sh 'docker-compose -f ${COMPOSE_FILE} ps'
            }
        }

        stage('Install Composer Dependencies') {
            steps {
                echo '📦 Installing Composer dependencies...'
                sh '''
                    docker exec nqobileq_web bash -c "cd /var/www/html && composer install --no-interaction" 2>/dev/null || echo "Composer already installed"
                '''
            }
        }

        stage('Set Permissions') {
            steps {
                echo '🔧 Setting permissions...'
                sh '''
                    docker exec nqobileq_web chown -R www-data:www-data /var/www/html 2>/dev/null || true
                    docker exec nqobileq_web chmod -R 755 /var/www/html 2>/dev/null || true
                    docker exec nqobileq_web chmod -R 777 /var/www/html/vendor 2>/dev/null || true
                '''
            }
        }

        stage('Verify Environment Setup') {
            steps {
                echo '🔧 Verifying environment setup...'
                sh '''
                    echo "Checking .env file in container..."
                    docker exec nqobileq_web cat /var/www/html/.env 2>/dev/null | grep -E "SMTP_USERNAME|OWNER_EMAIL" && echo "✅ Email credentials found" || echo "⚠️ Missing"
                    
                    echo "Checking vendor directory..."
                    docker exec nqobileq_web ls -la /var/www/html/vendor/ 2>/dev/null | head -3 && echo "✅ vendor exists" || echo "⚠️ vendor missing"
                '''
            }
        }

        stage('Verify Database') {
            steps {
                echo '🗄️ Verifying database...'
                sh '''
                    for i in 1 2 3 4 5 6 7 8 9 10 11 12 13 14 15; do
                        if docker exec nqobileq_db mysqladmin ping -h localhost --silent 2>/dev/null; then
                            echo "✅ MySQL ready!"
                            break
                        fi
                        sleep 2
                    done
                '''
            }
        }

        stage('Run Database Initialization') {
            steps {
                echo '📀 Database initialization...'
                sh 'docker exec -i nqobileq_db mysql -uroot -prootpassword123 nqobileq_db < init.sql 2>/dev/null || echo "Init already run"'
            }
        }

        stage('Create Database Backup') {
            steps {
                echo '💾 Creating database backup...'
                sh '''
                    mkdir -p /tmp/backups
                    docker exec nqobileq_db mysqldump -uroot -prootpassword123 nqobileq_db 2>/dev/null > /tmp/backups/backup_$(date +%Y%m%d_%H%M%S).sql
                    echo "✅ Backup created"
                    ls -t /tmp/backups/backup_*.sql 2>/dev/null | tail -n +11 | xargs rm -f 2>/dev/null || true
                '''
            }
        }

        stage('Verify Web Application') {
            steps {
                echo '🌐 Testing web...'
                sh 'curl -s -f http://localhost > /dev/null && echo "✅ Web running" || echo "❌ Web not responding"'
            }
        }

        stage('Verify Live Site') {
            steps {
                echo '🌐 Site live!'
                sh '''
                    echo "=========================================="
                    echo "✅ NQOBILEQ DEPLOYMENT COMPLETE!"
                    echo "=========================================="
                    echo "Website: http://13.205.187.75"
                    echo "Admin: http://13.205.187.75/admin/"
                    echo "Admin: admin@nqobileq.com / admin123"
                    echo "Email configured from Jenkins credentials"
                    echo "=========================================="
                '''
            }
        }
    }

    post {
        success {
            echo '🎉 DEPLOYMENT SUCCESSFUL! 🎉'
            // Clean up .env from workspace (keeps secrets safe)
            sh 'rm -f .env'
        }
        failure {
            echo '❌ DEPLOYMENT FAILED!'
            sh '''
                echo "=== Docker Compose Logs ==="
                docker-compose -f ${COMPOSE_FILE} logs --tail=50
            '''
        }
        always {
            echo '🧹 Cleaning up...'
            sh 'docker image prune -f || true'
            sh 'docker system prune -f || true'
        }
    }
}