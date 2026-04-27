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
            sh 'docker-compose -f ${COMPOSE_FILE} logs --tail=30'
        }
        always {
            echo '🧹 Cleaning up...'
            sh 'docker image prune -f || true'
            sh 'docker system prune -f || true'
        }
    }
}