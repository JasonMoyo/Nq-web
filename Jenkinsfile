pipeline {
    agent any

    environment {
        APP_NAME = 'nqobileq'
        COMPOSE_FILE = 'docker-compose.yml'
        WORKSPACE = '/var/lib/jenkins/workspace/NqoQ'
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

        stage('Install PHP Dependencies') {
            steps {
                echo '📦 Installing PHP dependencies with Composer...'
                sh '''
                    echo "Installing Composer and dependencies..."
                    
                    # Install Composer in container
                    docker exec nqobileq_web bash -c "if [ ! -f /usr/local/bin/composer ]; then php -r \"copy('https://getcomposer.org/installer', 'composer-setup.php');\" && php composer-setup.php --quiet && php -r \"unlink('composer-setup.php');\" && mv composer.phar /usr/local/bin/composer && chmod +x /usr/local/bin/composer; fi"
                    
                    # Install PHP dependencies
                    docker exec nqobileq_web bash -c "cd /var/www/html && composer install --no-interaction --no-progress"
                    
                    # Fix permissions
                    docker exec nqobileq_web chown -R www-data:www-data /var/www/html/vendor
                    docker exec nqobileq_web chmod -R 755 /var/www/html/vendor
                    
                    echo "✅ Composer dependencies installed successfully"
                '''
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
                    
                    echo "Testing vendor autoload..."
                    docker exec nqobileq_web php -r "require 'vendor/autoload.php'; echo '✅ Autoload OK';" || exit 1
                    
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
                        echo "⚠️ Admin user not found - creating..."
                        docker exec nqobileq_db mysql -uroot -prootpassword123 -e "INSERT INTO nqobileq_db.users (full_name, email, phone, password, is_admin) VALUES ('Admin', 'admin@nqobileq.com', '+27782280408', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1);"
                    fi
                '''
            }
        }

        stage('Create Backup') {
            steps {
                echo '💾 Creating database backup...'
                sh '''
                    mkdir -p /home/ubuntu/backups
                    docker exec nqobileq_db mysqldump -uroot -prootpassword123 nqobileq_db > /home/ubuntu/backups/backup_$(date +%Y%m%d_%H%M%S).sql
                    echo "✅ Database backup created"
                    
                    # Keep only last 10 backups
                    ls -t /home/ubuntu/backups/backup_*.sql | tail -n +11 | xargs rm -f 2>/dev/null || true
                '''
            }
        }

        stage('Verify Live Site') {
            steps {
                echo '🌐 Verifying live site...'
                sh '''
                    PUBLIC_IP=$(curl -s ifconfig.me 2>/dev/null || echo "13.205.187.75")
                    echo "=========================================="
                    echo "✅ DEPLOYMENT SUCCESSFUL!"
                    echo "=========================================="
                    echo "Website:      http://$PUBLIC_IP"
                    echo "Admin Panel:  http://$PUBLIC_IP/admin/"
                    echo "phpMyAdmin:   http://$PUBLIC_IP:8081"
                    echo ""
                    echo "Admin Login:"
                    echo "  Email: admin@nqobileq.com"
                    echo "  Password: admin123"
                    echo "=========================================="
                    
                    curl -f http://localhost && echo "✅ Site is live!"
                '''
            }
        }
    }

    post {
        success {
            echo '🎉 NQOBILEQ DEPLOYMENT COMPLETED SUCCESSFULLY! 🎉'
            
            // Optional: Send email notification
            emailext(
                subject: "✅ NqobileQ Build Successful - Build #${env.BUILD_NUMBER}",
                body: """
                    NqobileQ has been successfully deployed!
                    
                    Build Information:
                    - Build Number: ${env.BUILD_NUMBER}
                    - Build URL: ${env.BUILD_URL}
                    
                    Access the application at:
                    http://13.205.187.75
                    
                    Admin Login: admin@nqobileq.com / admin123
                """,
                to: 'thabani070801@gmail.com'
            )
        }
        
        failure {
            echo '❌ DEPLOYMENT FAILED! Check the logs above.'
            
            // Show error logs
            sh '''
                echo "=== Docker Compose Logs ==="
                docker-compose -f ${COMPOSE_FILE} logs --tail=50
                
                echo "=== Web Container Logs ==="
                docker logs nqobileq_web --tail=30 2>/dev/null || echo "Web container not running"
                
                echo "=== Database Container Logs ==="
                docker logs nqobileq_db --tail=30 2>/dev/null || echo "Database container not running"
            '''
            
            // Optional: Send failure notification
            emailext(
                subject: "❌ NqobileQ Build Failed - Build #${env.BUILD_NUMBER}",
                body: "The build has failed. Check Jenkins console for details: ${env.BUILD_URL}",
                to: 'thabani070801@gmail.com'
            )
        }
        
        always {
            echo '🧹 Cleaning up old Docker resources...'
            sh 'docker image prune -f || true'
            sh 'docker system prune -f || true'
        }
    }
}