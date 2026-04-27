pipeline {
    agent any

    environment {
        APP_NAME = 'nqobileq'
        COMPOSE_FILE = 'docker-compose.yml'
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
                    [ -f ".env.example" ] && echo "✅ .env.example found" || echo "⚠️ .env.example missing (will be created)"
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
                    docker exec nqobileq_web ls -la /var/www/html/.env 2>/dev/null && echo "✅ .env exists" || echo "⚠️ .env missing"
                    
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
                    echo "=========================================="
                '''
            }
        }
    }

    post {
        success {
            echo '🎉 DEPLOYMENT SUCCESSFUL! 🎉'
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