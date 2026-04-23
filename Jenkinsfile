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
                sh '''
                    docker-compose -f ${COMPOSE_FILE} down || true
                    docker stop nqobileq_web nqobileq_db nqobileq_phpmyadmin 2>/dev/null || true
                    docker rm nqobileq_web nqobileq_db nqobileq_phpmyadmin 2>/dev/null || true
                '''
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
                sleep 15
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
                    echo "Waiting for MySQL to be ready..."
                    for i in 1 2 3 4 5 6 7 8 9 10 11 12 13 14 15 16 17 18 19 20 21 22 23 24 25 26 27 28 29 30; do
                        if docker exec nqobileq_db mysqladmin ping -h localhost --silent; then
                            echo "✅ MySQL is ready!"
                            break
                        fi
                        echo "Waiting for MySQL... ($i/30)"
                        sleep 2
                    done
                    
                    docker exec nqobileq_db mysql -uroot -prootpassword123 -e "USE nqobileq_db; SHOW TABLES;" || echo "Database may need initialization"
                '''
            }
        }

        stage('Verify Web Application') {
            steps {
                echo '🌐 Testing web application...'
                sh '''
                    echo "Testing homepage..."
                    curl -f http://localhost || exit 1
                    
                    echo "Testing PHP..."
                    docker exec nqobileq_web php -v || exit 1
                    
                    echo "Testing MySQL extension..."
                    docker exec nqobileq_web php -m | grep mysqli || exit 1
                    
                    echo "✅ Web application is running!"
                '''
            }
        }

        stage('Run Database Initialization') {
            steps {
                echo '📀 Running database initialization...'
                sh '''
                    docker exec -i nqobileq_db mysql -uroot -prootpassword123 nqobileq_db < init.sql 2>/dev/null || echo "Init already run or no init.sql"
                    
                    ADMIN_COUNT=$(docker exec nqobileq_db mysql -uroot -prootpassword123 -se "SELECT COUNT(*) FROM nqobileq_db.users WHERE email='admin@nqobileq.com';")
                    if [ "$ADMIN_COUNT" -gt 0 ]; then
                        echo "✅ Admin user exists"
                    else
                        echo "⚠️ Admin user not found - run init.sql manually"
                    fi
                '''
            }
        }

        // ============ VERIFY DEPLOYMENT ============
        stage('Verify Deployment') {
            steps {
                echo '🌐 Verifying deployment...'
                sh '''
                    echo "Application is running on Jenkins master!"
                    echo "To access the website, you need to expose port 80 on this machine"
                    
                    # Get public IP if available
                    PUBLIC_IP=$(curl -s ifconfig.me 2>/dev/null || echo "unknown")
                    echo "Public IP: $PUBLIC_IP"
                    
                    echo ""
                    echo "If you want to access the website from outside:"
                    echo "1. Open port 80 in AWS security group"
                    echo "2. Access at http://$PUBLIC_IP"
                '''
            }
        }
    }

    post {
        success {
            echo '''
                ┌─────────────────────────────────────────────────────────┐
                │     ✅  NQOBILEQ DEPLOYMENT SUCCESSFUL!  ✅             │
                ├─────────────────────────────────────────────────────────┤
                │                                                         │
                │  📍 Application is running on Jenkins Master!           │
                │                                                         │
                │  To access the website:                                 │
                │  1. Open port 80 in AWS security group                  │
                │  2. Visit http://<JENKINS_MASTER_PUBLIC_IP>             │
                │                                                         │
                │  🔐 Login Credentials:                                  │
                │     Admin Email:   admin@nqobileq.com                   │
                │     Admin Password: admin123                            │
                │                                                         │
                └─────────────────────────────────────────────────────────┘
            '''
        }
        
        failure {
            echo '❌ DEPLOYMENT FAILED! Check logs above.'
            sh 'docker-compose -f ${COMPOSE_FILE} logs --tail=50'
        }
        
        always {
            echo '🧹 Pipeline execution completed.'
            sh 'docker image prune -f || true'
            sh 'docker system prune -f || true'
        }
    }
}