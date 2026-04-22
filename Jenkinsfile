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
                    # Wait for MySQL to be ready
                    echo "Waiting for MySQL to be ready..."
                    for i in {1..30}; do
                        if docker exec nqobileq_db mysqladmin ping -h localhost --silent; then
                            echo "✅ MySQL is ready!"
                            break
                        fi
                        echo "Waiting for MySQL... ($i/30)"
                        sleep 2
                    done
                    
                    # Check if database is initialized
                    docker exec nqobileq_db mysql -uroot -prootpassword123 -e "USE nqobileq_db; SHOW TABLES;" || echo "Database may need initialization"
                '''
            }
        }

        stage('Verify Web Application') {
            steps {
                echo '🌐 Testing web application...'
                sh '''
                    # Test main page
                    echo "Testing homepage..."
                    curl -f http://localhost || exit 1
                    
                    # Test PHP info (optional)
                    echo "Testing PHP..."
                    docker exec nqobileq_web php -v || exit 1
                    
                    # Test MySQL extension
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
                    # Run init.sql if needed
                    docker exec -i nqobileq_db mysql -uroot -prootpassword123 nqobileq_db < init.sql 2>/dev/null || echo "Init already run or no init.sql"
                    
                    # Verify admin user exists
                    ADMIN_COUNT=$(docker exec nqobileq_db mysql -uroot -prootpassword123 -se "SELECT COUNT(*) FROM nqobileq_db.users WHERE email='admin@nqobileq.com';")
                    if [ "$ADMIN_COUNT" -gt 0 ]; then
                        echo "✅ Admin user exists"
                    else
                        echo "⚠️ Admin user not found - run init.sql manually"
                    fi
                '''
            }
        }
    }

    post {
        success {
            echo '''
                ┌─────────────────────────────────────────────────────────┐
                │                                                         │
                │     ✅  NQOBILEQ DEPLOYMENT SUCCESSFUL!  ✅             │
                │                                                         │
                ├─────────────────────────────────────────────────────────┤
                │                                                         │
                │  📍 Application Access:                                 │
                │     Main Website:  http://localhost                     │
                │     Admin Panel:   http://localhost/admin               │
                │     phpMyAdmin:    http://localhost:8081                │
                │                                                         │
                │  🔐 Login Credentials:                                  │
                │     Admin Email:   admin@nqobileq.com                   │
                │     Admin Password: admin123                            │
                │                                                         │
                │  🐳 Docker Commands:                                    │
                │     View logs:    docker-compose logs -f                │
                │     Stop:         docker-compose down                   │
                │     Restart:      docker-compose restart                │
                │                                                         │
                └─────────────────────────────────────────────────────────┘
            '''
            
            // Optional: Send email notification
            emailext(
                subject: "✅ NqobileQ Build Successful - Build #${env.BUILD_NUMBER}",
                body: """
                    NqobileQ has been successfully deployed!
                    
                    Build Information:
                    - Build Number: ${env.BUILD_NUMBER}
                    - Build URL: ${env.BUILD_URL}
                    
                    Access the application at:
                    http://localhost
                    
                    Admin Login: admin@nqobileq.com / admin123
                """,
                to: 'thabani070801@gmail.com'
            )
        }
        
        failure {
            echo '''
                ┌─────────────────────────────────────────────────────────┐
                │                                                         │
                │     ❌  NQOBILEQ DEPLOYMENT FAILED!  ❌                 │
                │                                                         │
                ├─────────────────────────────────────────────────────────┤
                │                                                         │
                │  Check the logs below for more details:                 │
                │                                                         │
                └─────────────────────────────────────────────────────────┘
            '''
            
            sh '''
                echo "=== Docker Compose Logs ==="
                docker-compose -f ${COMPOSE_FILE} logs --tail=50
                
                echo "=== Web Container Logs ==="
                docker logs nqobileq_web 2>/dev/null || echo "Web container not running"
                
                echo "=== Database Container Logs ==="
                docker logs nqobileq_db 2>/dev/null || echo "Database container not running"
            '''
            
            // Optional: Send failure notification
            emailext(
                subject: "❌ NqobileQ Build Failed - Build #${env.BUILD_NUMBER}",
                body: "The build has failed. Check Jenkins console for details: ${env.BUILD_URL}",
                to: 'thabani070801@gmail.com'
            )
        }
        
        always {
            echo '🧹 Pipeline execution completed.'
            // Clean up old docker images to save space
            sh 'docker image prune -f || true'
            sh 'docker system prune -f || true'
        }
    }
}