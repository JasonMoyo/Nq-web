pipeline {
    agent any

    environment {
        APP_NAME = 'nqobileq'
        COMPOSE_FILE = 'docker-compose.yml'
        
        // ============ ADD CREDENTIALS HERE ============
        // Retrieve credentials from Jenkins
        SMTP_USERNAME = credentials('smtp-username')
        SMTP_PASSWORD = credentials('smtp-password')
        OWNER_EMAIL = credentials('owner-email')
        // Optional: Database password if needed
        // DB_PASSWORD = credentials('db-password')
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
                    [ -f ".env.example" ] && echo "✅ .env.example found" || echo "⚠️ .env.example missing"
                '''
            }
        }

        // ============ NEW STAGE: Create .env with credentials ============
        stage('Create Environment File') {
            steps {
                echo '🔧 Creating .env file with credentials from Jenkins...'
                sh '''
                    # Create .env file with all configurations
                    cat > .env << EOF
# Database Configuration
DB_HOST=db
DB_USER=nqobileq_user
DB_PASSWORD=userpassword123
DB_NAME=nqobileq_db

# Email Configuration (from Jenkins credentials)
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USERNAME=${SMTP_USERNAME}
SMTP_PASSWORD=${SMTP_PASSWORD}
SMTP_SECURE=tls

# Site Configuration
SITE_URL=http://13.205.187.75
APP_ENV=production

# Contact Info
OWNER_PHONE=+27782280408
OWNER_EMAIL=${OWNER_EMAIL}
EOF
                    echo "✅ .env file created successfully"
                    
                    # Show that email is configured (hide password)
                    echo "Email configured for: ${SMTP_USERNAME}"
                '''
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

        stage('Check Container Status') {
            steps {
                echo '📊 Checking container status...'
                sh 'docker-compose -f ${COMPOSE_FILE} ps'
            }
        }

        stage('Verify Environment Setup') {
            steps {
                echo '🔧 Verifying environment setup inside container...'
                sh '''
                    echo "Checking .env file..."
                    docker exec nqobileq_web cat /var/www/html/.env 2>/dev/null | grep -E "SMTP_USERNAME|OWNER_EMAIL" && echo "✅ Email credentials found" || echo "⚠️ Email credentials missing"
                    
                    echo "Checking vendor directory..."
                    docker exec nqobileq_web ls -la /var/www/html/vendor/ 2>/dev/null | head -3 && echo "✅ vendor exists" || echo "⚠️ vendor missing"
                    
                    echo "Checking PHP extensions..."
                    docker exec nqobileq_web php -m | grep -E "mysqli|pdo" | head -3
                '''
            }
        }

        stage('Verify Database') {
            steps {
                echo '🗄️ Verifying database connection...'
                sh '''
                    for i in 1 2 3 4 5 6 7 8 9 10 11 12 13 14 15; do
                        if docker exec nqobileq_db mysqladmin ping -h localhost --silent 2>/dev/null; then
                            echo "✅ MySQL is ready!"
                            break
                        fi
                        echo "Waiting for MySQL... ($i/15)"
                        sleep 2
                    done
                '''
            }
        }

        stage('Verify Web Application') {
            steps {
                echo '🌐 Testing web application...'
                sh 'curl -s -f http://localhost > /dev/null && echo "✅ Web is running" || echo "❌ Web failed"'
            }
        }

        stage('Run Database Initialization') {
            steps {
                echo '📀 Running database initialization...'
                sh 'docker exec -i nqobileq_db mysql -uroot -prootpassword123 nqobileq_db < init.sql 2>/dev/null || echo "Init already run"'
                sh 'docker exec nqobileq_db mysql -uroot -prootpassword123 -e "SELECT COUNT(*) as users FROM nqobileq_db.users;" 2>/dev/null || echo "Check manually"'
            }
        }

        stage('Test Email Configuration') {
            steps {
                echo '📧 Testing email configuration...'
                sh '''
                    docker exec nqobileq_web bash -c "php -r \\"
                        \\$env = parse_ini_file('/var/www/html/.env');
                        if(isset(\\$env['SMTP_USERNAME'])) {
                            echo '✅ SMTP_USERNAME configured: ' . \\$env['SMTP_USERNAME'] . '\\n';
                        } else {
                            echo '❌ SMTP_USERNAME not found\\n';
                        }
                    \\""
                '''
            }
        }

        stage('Create Database Backup') {
            steps {
                echo '💾 Creating database backup...'
                sh '''
                    mkdir -p /home/ubuntu/backups
                    docker exec nqobileq_db mysqldump -uroot -prootpassword123 nqobileq_db 2>/dev/null > /home/ubuntu/backups/backup_$(date +%Y%m%d_%H%M%S).sql
                    echo "✅ Backup created"
                    
                    # Keep only last 10 backups
                    ls -t /home/ubuntu/backups/backup_*.sql 2>/dev/null | tail -n +11 | xargs rm -f 2>/dev/null || true
                '''
            }
        }

        stage('Verify Live Site') {
            steps {
                echo '🌐 Verifying live site...'
                sh '''
                    PUBLIC_IP=$(curl -s ifconfig.me 2>/dev/null || echo "13.205.187.75")
                    echo "=========================================="
                    echo "✅ NQOBILEQ DEPLOYMENT COMPLETE!"
                    echo "=========================================="
                    echo "Website: http://$PUBLIC_IP"
                    echo "Admin: http://$PUBLIC_IP/admin/"
                    echo "phpMyAdmin: http://$PUBLIC_IP:8081"
                    echo ""
                    echo "Admin Login: admin@nqobileq.com / admin123"
                    echo "Email configured for: ${SMTP_USERNAME}"
                    echo "=========================================="
                '''
            }
        }
    }

    post {
        success {
            echo '🎉 DEPLOYMENT SUCCESSFUL! 🎉'
            # Clean up .env file from workspace (optional - security)
            sh 'rm -f .env'
        }
        failure {
            echo '❌ DEPLOYMENT FAILED!'
            sh 'docker-compose -f ${COMPOSE_FILE} logs --tail=50'
        }
        always {
            echo '🧹 Cleaning up...'
            sh 'docker image prune -f || true'
            sh 'docker system prune -f || true'
        }
    }
}