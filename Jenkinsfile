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

        // ============ DEPLOY TO AGENT EC2 ============
        stage('Deploy to Agent EC2') {
            steps {
                echo '🚀 Deploying to Agent EC2...'
                sh """
                    ssh ubuntu@172.31.40.110 << 'ENDSSH'
                        cd /var/www/html
                        
                        echo "📦 Pulling latest code from GitHub..."
                        git pull origin main
                        
                        echo "🛑 Stopping existing containers..."
                        docker-compose -f docker-compose.yml down
                        
                        echo "🏗️ Rebuilding Docker images..."
                        docker-compose -f docker-compose.yml build --no-cache
                        
                        echo "🚀 Starting containers..."
                        docker-compose -f docker-compose.yml up -d
                        
                        echo "⏳ Waiting for containers to be ready..."
                        sleep 10
                        
                        echo "📊 Container status:"
                        docker-compose -f docker-compose.yml ps
                        
                        echo "🌐 Testing website locally..."
                        curl -f http://localhost && echo "✅ Website is running on agent!"
ENDSSH
                """
            }
        }

        // ============ VERIFY LIVE WEBSITE ============
        stage('Verify Live Website') {
            steps {
                echo '🌐 Verifying live website...'
                sh '''
                    echo "Testing website at http://3.7.14.58"
                    curl -f http://3.7.14.58 && echo "✅ Website is LIVE with latest changes!"
                    
                    echo ""
                    echo "Testing admin panel..."
                    curl -f http://3.7.14.58/admin/ && echo "✅ Admin panel accessible" || echo "Admin panel requires login"
                    
                    echo ""
                    echo "Testing phpMyAdmin..."
                    curl -f http://3.7.14.58:8081 && echo "✅ phpMyAdmin accessible" || echo "phpMyAdmin may not be accessible"
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
                │     Main Website:  http://3.7.14.58                     │
                │     Admin Panel:   http://3.7.14.58/admin               │
                │     phpMyAdmin:    http://3.7.14.58:8081                │
                │                                                         │
                │  🔐 Login Credentials:                                  │
                │     Admin Email:   admin@nqobileq.com                   │
                │     Admin Password: admin123                            │
                │                                                         │
                │  🔄 Auto-Deploy Active:                                 │
                │     Future pushes will automatically update the site!   │
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
                    http://3.7.14.58
                    
                    Admin Login: admin@nqobileq.com / admin123
                    
                    Your changes are now LIVE on the website!
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
                echo "=== Docker Compose Logs (Jenkins Master) ==="
                docker-compose -f ${COMPOSE_FILE} logs --tail=50
                
                echo "=== Web Container Logs ==="
                docker logs nqobileq_web 2>/dev/null || echo "Web container not running"
                
                echo "=== Database Container Logs ==="
                docker logs nqobileq_db 2>/dev/null || echo "Database container not running"
                
                echo "=== Agent EC2 Logs ==="
                ssh ubuntu@172.31.40.110 "cd /var/www/html && docker-compose logs --tail=50" 2>/dev/null || echo "Cannot connect to agent"
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
            
            // Clean up on agent as well
            ssh ubuntu@172.31.40.110 "docker image prune -f && docker system prune -f" 2>/dev/null || true
        }
    }
}