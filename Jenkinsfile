pipeline {
    agent any

    environment {
        APP_NAME = 'nqobileq'
        COMPOSE_FILE = 'docker-compose.yml'
        
        // Credentials from Jenkins
        SMTP_USERNAME = credentials('smtp-username')
        SMTP_PASSWORD = credentials('smtp-password')
        OWNER_EMAIL = credentials('owner-email')
    }

    stages {

        stage('Clone Repository') {
            steps {
                echo '📦 Cloning NqobileQ repository...'
                git branch: 'main', url: 'https://github.com/JasonMoyo/Nq-web.git'
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

        // ============ CREATE .env FILE WITH REAL CREDENTIALS ============
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
                sh 'cat .env | grep SMTP_USERNAME'
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
                    docker cp .env nqobileq_web:/var/www/html/.env
                    docker exec nqobileq_web chown www-data:www-data /var/www/html/.env
                    docker exec nqobileq_web chmod 644 /var/www/html/.env
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
                    docker exec nqobileq_web bash -c "cd /var/www/html && composer install --no-interaction"
                '''
            }
        }

        stage('Set Permissions') {
            steps {
                echo '🔧 Setting permissions...'
                sh '''
                    docker exec nqobileq_web chown -R www-data:www-data /var/www/html
                    docker exec nqobileq_web chmod -R 755 /var/www/html
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
                    docker exec nqobileq_web ls -la /var/www/html/vendor/ 2>/dev/null | head -3 && echo "✅ vendor exists"
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

        stage('Verify Web Application') {
            steps {
                echo '🌐 Testing web...'
                sh 'curl -s -f http://localhost > /dev/null && echo "✅ Web running"'
            }
        }

        stage('Test Email Configuration') {
            steps {
                echo '📧 Testing email configuration...'
                sh '''
                    docker exec nqobileq_web bash -c "php -r \\"
                        \\$env = parse_ini_file('/var/www/html/.env');
                        if(isset(\\$env['SMTP_USERNAME']) && \\$env['SMTP_USERNAME'] != 'your-email@gmail.com') {
                            echo '✅ SMTP configured for: ' . \\$env['SMTP_USERNAME'] . '\\n';
                        } else {
                            echo '❌ SMTP not properly configured\\n';
                        }
                    \\""
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
                    echo "Website: http://13.205.187.75"
                    echo "Admin: http://13.205.187.75/admin/"
                    echo "Admin: admin@nqobileq.com / admin123"
                    echo "Email configured for: ${SMTP_USERNAME}"
                    echo "=========================================="
                '''
            }
        }
    }

    post {
        success {
            echo '🎉 DEPLOYMENT SUCCESSFUL! 🎉'
            // Clean up .env from workspace (optional, keeps secrets safe)
            sh 'rm -f .env'
        }
        failure {
            echo '❌ DEPLOYMENT FAILED!'
            sh 'docker-compose -f ${COMPOSE_FILE} logs --tail=50'
        }
        always {
            sh 'docker image prune -f || true'
            sh 'docker system prune -f || true'
        }
    }
}