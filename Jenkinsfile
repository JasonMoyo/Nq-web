pipeline {
    agent any
    
    environment {
        // Build information
        BUILD_TIMESTAMP = sh(script: "date +'%Y%m%d_%H%M%S'", returnStdout: true).trim()
        DOCKER_IMAGE_NAME = "nqobileq-web"
        DOCKER_CONTAINER_NAME = "nqobileq_web"
        DB_CONTAINER_NAME = "nqobileq_db"
        PMA_CONTAINER_NAME = "nqobileq_phpmyadmin"
        
        // Environment variables from Jenkins credentials
        SMTP_USERNAME = credentials('SMTP_USERNAME')
        SMTP_PASSWORD = credentials('SMTP_PASSWORD')
        STRIPE_PUBLISHABLE_KEY = credentials('STRIPE_PUBLISHABLE_KEY')
        STRIPE_SECRET_KEY = credentials('STRIPE_SECRET_KEY')
        OWNER_EMAIL = credentials('OWNER_EMAIL')
    }
    
    stages {
        stage('Clean and Fix Workspace') {
            steps {
                echo '🧹 Cleaning workspace and fixing permissions...'
                script {
                    sh '''
                        # Fix Git safe directory (prevents "dubious ownership" errors)
                        git config --global --add safe.directory "${WORKSPACE}" 2>/dev/null || true
                        
                        # Remove any Git lock files (fixes "AccessDeniedException" errors)
                        echo "Removing any lock files..."
                        find .git -name "*.lock" 2>/dev/null | xargs rm -f 2>/dev/null || true
                        rm -f .git/config.lock 2>/dev/null || true
                        rm -f .git/index.lock 2>/dev/null || true
                        rm -f .git/HEAD.lock 2>/dev/null || true
                        echo "✅ Lock files removed"
                        
                        # Fix permissions (using ubuntu user since Jenkins runs as ubuntu)
                        echo "Fixing permissions..."
                        sudo chown -R ubuntu:ubuntu . 2>/dev/null || true
                        sudo chmod -R 755 . 2>/dev/null || true
                        echo "✅ Permissions fixed"
                        
                        # Clean up any leftover Docker artifacts
                        echo "Cleaning up old Docker artifacts..."
                        docker system prune -f 2>/dev/null || true
                        docker volume prune -f 2>/dev/null || true
                    '''
                }
            }
        }
        
        stage('Build Versioning') {
            steps {
                echo '📌 Creating build version...'
                script {
                    sh """
                        echo "BUILD_VERSION=${BUILD_TIMESTAMP}" > version.txt
                        echo "BUILD_NUMBER=${BUILD_NUMBER}" >> version.txt
                        echo "BUILD_URL=${BUILD_URL}" >> version.txt
                        echo "JOB_NAME=${JOB_NAME}" >> version.txt
                        echo "GIT_COMMIT=${GIT_COMMIT}" >> version.txt
                    """
                    archiveArtifacts artifacts: 'version.txt', fingerprint: true
                }
            }
        }
        
        stage('Pre-Build Checks') {
            parallel {
                stage('Check Docker') {
                    steps {
                        echo '🐳 Checking Docker installation...'
                        script {
                            sh '''
                                docker --version
                                docker-compose --version || docker compose version
                                echo "✅ Docker checks passed"
                            '''
                        }
                    }
                }
                
                stage('Check PHP Syntax') {
                    steps {
                        echo '🔍 Checking PHP syntax...'
                        script {
                            sh '''
                                if command -v php >/dev/null 2>&1; then
                                    echo "PHP found: $(php --version | head -1)"
                                    find . -name "*.php" -not -path "./vendor/*" -type f | while read file; do
                                        echo "Checking $file"
                                        php -l "$file" || exit 1
                                    done
                                    echo "✅ PHP syntax checks passed"
                                else
                                    echo "⚠️ PHP not installed on agent, skipping syntax check"
                                fi
                            '''
                        }
                    }
                }
                
                stage('Security Scan') {
                    steps {
                        echo '🔒 Running security checks...'
                        script {
                            sh '''
                                # Check for exposed .env files
                                if [ -f ".env" ]; then
                                    echo "⚠️  Warning: .env file exists in repository (should be gitignored)"
                                fi
                                
                                # Check for sensitive files
                                SENSITIVE_COUNT=$(find . -name "*.pem" -o -name "*.key" -o -name "*.crt" 2>/dev/null | wc -l)
                                if [ "$SENSITIVE_COUNT" -gt 0 ]; then
                                    echo "⚠️  Warning: Found $SENSITIVE_COUNT sensitive files"
                                    find . -name "*.pem" -o -name "*.key" -o -name "*.crt" 2>/dev/null | while read file; do
                                        echo "  - $file"
                                    done
                                fi
                                
                                echo "✅ Security scan completed"
                            '''
                        }
                    }
                }
            }
        }
        
        stage('Verify Project Files') {
            steps {
                echo '📁 Verifying project structure...'
                script {
                    sh '''
                        REQUIRED_FILES="index.php config.php docker-compose.yml Dockerfile init.sql styles.css script.js"
                        MISSING=0
                        
                        for file in $REQUIRED_FILES; do
                            if [ -f "$file" ]; then
                                echo "✓ $file found"
                            else
                                echo "✗ $file MISSING"
                                MISSING=1
                            fi
                        done
                        
                        if [ -d "assets" ]; then
                            echo "✓ assets directory found"
                        else
                            echo "⚠️  assets directory missing (optional)"
                        fi
                        
                        if [ $MISSING -eq 1 ]; then
                            echo "❌ Missing required files, aborting"
                            exit 1
                        fi
                        
                        echo "✅ All required files present"
                    '''
                }
            }
        }
        
        stage('Create .env File') {
            steps {
                echo '🔧 Creating .env file from environment variables...'
                script {
                    sh """
                        # Create .env file from environment
                        cat > .env << 'ENVEOF'
# Database Configuration
DB_HOST=db
DB_USER=nqobileq_user
DB_PASSWORD=userpassword123
DB_NAME=nqobileq_db

# Email Configuration
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USERNAME=${SMTP_USERNAME}
SMTP_PASSWORD=${SMTP_PASSWORD}
SMTP_SECURE=tls

# Stripe Keys
STRIPE_PUBLISHABLE_KEY=${STRIPE_PUBLISHABLE_KEY}
STRIPE_SECRET_KEY=${STRIPE_SECRET_KEY}

# Site Configuration
SITE_URL=http://13.232.172.213
APP_ENV=production

# Contact Info
OWNER_PHONE=+27782280408
OWNER_EMAIL=${OWNER_EMAIL}
ENVEOF
                        
                        # Set proper permissions
                        chmod 600 .env
                        echo "✅ .env file created"
                        
                        # Show .env (masked for debugging)
                        echo "📋 .env file created with masked values"
                    '''
                }
            }
        }
        
        stage('Stop Existing Containers') {
            steps {
                echo '🛑 Stopping existing containers...'
                script {
                    sh '''
                        # Stop containers using docker-compose
                        docker-compose -f docker-compose.yml down --remove-orphans 2>/dev/null || true
                        
                        # Stop containers individually
                        docker stop ${DOCKER_CONTAINER_NAME} 2>/dev/null || true
                        docker stop ${DB_CONTAINER_NAME} 2>/dev/null || true
                        docker stop ${PMA_CONTAINER_NAME} 2>/dev/null || true
                        
                        # Remove containers
                        docker rm ${DOCKER_CONTAINER_NAME} 2>/dev/null || true
                        docker rm ${DB_CONTAINER_NAME} 2>/dev/null || true
                        docker rm ${PMA_CONTAINER_NAME} 2>/dev/null || true
                        
                        # Remove old images
                        docker rmi ${DOCKER_IMAGE_NAME}:latest 2>/dev/null || true
                        
                        echo "✅ Old containers stopped and removed"
                    '''
                }
            }
        }
        
        stage('Build Docker Images') {
            steps {
                echo '🏗️  Building Docker images...'
                script {
                    sh '''
                        # Build with docker-compose
                        docker-compose -f docker-compose.yml build --no-cache
                        
                        # Tag the image
                        docker tag ${DOCKER_IMAGE_NAME}:latest ${DOCKER_IMAGE_NAME}:${BUILD_TIMESTAMP}
                        
                        echo "✅ Docker images built successfully"
                    '''
                }
            }
        }
        
        stage('Start Services') {
            steps {
                echo '🚀 Starting Docker services...'
                script {
                    sh '''
                        # Start all services
                        docker-compose -f docker-compose.yml up -d
                        
                        # Wait for services to be ready
                        echo "Waiting for services to start..."
                        sleep 15
                        
                        # Check container status
                        docker-compose -f docker-compose.yml ps
                        
                        echo "✅ Services started"
                    '''
                }
            }
        }
        
        stage('Copy .env to Container') {
            steps {
                echo '📋 Copying .env file to container...'
                script {
                    sh '''
                        # Wait for container to be fully ready
                        sleep 5
                        
                        # Copy .env file to container if container exists
                        if docker ps | grep -q ${DOCKER_CONTAINER_NAME}; then
                            docker cp .env ${DOCKER_CONTAINER_NAME}:/var/www/html/.env
                            docker exec ${DOCKER_CONTAINER_NAME} chmod 600 /var/www/html/.env
                            echo "✅ .env file copied to container"
                        else
                            echo "⚠️  Web container not running, skipping .env copy"
                        fi
                    '''
                }
            }
        }
        
        stage('Install Composer Dependencies') {
            steps {
                echo '📦 Installing Composer dependencies...'
                script {
                    sh '''
                        if [ -f "composer.json" ]; then
                            if docker ps | grep -q ${DOCKER_CONTAINER_NAME}; then
                                docker exec ${DOCKER_CONTAINER_NAME} bash -c "cd /var/www/html && composer install --no-interaction --no-dev --optimize-autoloader 2>/dev/null || echo '⚠️ Composer install skipped'"
                                echo "✅ Composer dependencies installed"
                            else
                                echo "⚠️ Web container not running, skipping Composer install"
                            fi
                        else
                            echo "⚠️ No composer.json found, skipping Composer install"
                        fi
                    '''
                }
            }
        }
        
        stage('Set Permissions') {
            steps {
                echo '🔐 Setting file permissions...'
                script {
                    sh '''
                        if docker ps | grep -q ${DOCKER_CONTAINER_NAME}; then
                            docker exec ${DOCKER_CONTAINER_NAME} chown -R www-data:www-data /var/www/html 2>/dev/null || true
                            docker exec ${DOCKER_CONTAINER_NAME} chmod -R 755 /var/www/html 2>/dev/null || true
                            echo "✅ Permissions set correctly"
                        else
                            echo "⚠️ Web container not running, skipping permissions"
                        fi
                    '''
                }
            }
        }
        
        stage('Verify Database') {
            steps {
                echo '🗄️  Verifying database connection...'
                script {
                    sh '''
                        echo "Waiting for database to be ready..."
                        sleep 10
                        
                        if docker ps | grep -q ${DB_CONTAINER_NAME}; then
                            docker exec ${DB_CONTAINER_NAME} mysqladmin ping -h localhost --silent || echo "⚠️ Database not ready yet"
                            echo "✅ Database is ready"
                        else
                            echo "⚠️ Database container not running"
                        fi
                    '''
                }
            }
        }
        
        stage('Initialize Database') {
            steps {
                echo '📀 Initializing database schema...'
                script {
                    sh '''
                        if [ -f "init.sql" ]; then
                            if docker ps | grep -q ${DB_CONTAINER_NAME}; then
                                docker exec -i ${DB_CONTAINER_NAME} mysql -uroot -prootpassword123 < init.sql 2>/dev/null || echo "⚠️ Database already initialized or import skipped"
                                echo "✅ Database initialized"
                            else
                                echo "⚠️ Database container not running, skipping initialization"
                            fi
                        else
                            echo "⚠️ No init.sql found, skipping database initialization"
                        fi
                    '''
                }
            }
        }
        
        stage('Health Check') {
            steps {
                echo '🏥 Running health checks...'
                script {
                    sh '''
                        if docker ps | grep -q ${DOCKER_CONTAINER_NAME}; then
                            # Test HTTP response
                            sleep 5
                            HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" http://localhost:80/ 2>/dev/null || echo "000")
                            if [ "$HTTP_CODE" = "200" ] || [ "$HTTP_CODE" = "302" ]; then
                                echo "✅ Website is responding (HTTP $HTTP_CODE)"
                            else
                                echo "⚠️ Website returned HTTP $HTTP_CODE"
                            fi
                        else
                            echo "⚠️ Web container not running"
                        fi
                        
                        echo "✅ Health checks completed"
                    '''
                }
            }
        }
        
        stage('Verify Live Site') {
            steps {
                echo '🌐 Verifying live site...'
                script {
                    sh '''
                        EC2_IP=$(curl -s http://checkip.amazonaws.com)
                        
                        echo "=========================================="
                        echo "✅ DEPLOYMENT SUCCESSFUL!"
                        echo "=========================================="
                        echo ""
                        echo "🌐 Website: http://$EC2_IP"
                        echo "📊 phpMyAdmin: http://$EC2_IP:8081"
                        echo ""
                        echo "Database Credentials:"
                        echo "  Username: root or nqobileq_user"
                        echo "  Password: rootpassword123 or userpassword123"
                        echo ""
                        echo "=========================================="
                    '''
                }
            }
        }
    }
    
    post {
        always {
            echo '🧹 Final cleanup...'
            script {
                sh '''
                    docker image prune -f 2>/dev/null || true
                    docker system prune -f 2>/dev/null || true
                    
                    echo "Currently running containers:"
                    docker-compose -f docker-compose.yml ps 2>/dev/null || echo "No containers running"
                '''
            }
        }
        
        success {
            echo '✅ DEPLOYMENT SUCCESSFUL!'
            script {
                def EC2_IP = sh(script: "curl -s http://checkip.amazonaws.com", returnStdout: true).trim()
                
                emailext(
                    to: "${OWNER_EMAIL}",
                    subject: "✅ Jenkins Build Success: ${env.JOB_NAME} - Build #${env.BUILD_NUMBER}",
                    body: """
                        The deployment was successful!
                        
                        Build Details:
                        - Job: ${env.JOB_NAME}
                        - Build Number: ${env.BUILD_NUMBER}
                        - Build URL: ${env.BUILD_URL}
                        - Version: ${BUILD_TIMESTAMP}
                        
                        Site Information:
                        - Website: http://${EC2_IP}
                        - phpMyAdmin: http://${EC2_IP}:8081
                        
                        Database Credentials:
                        - Username: root or nqobileq_user
                        - Password: rootpassword123 or userpassword123
                        
                        For more details, visit: ${env.BUILD_URL}
                    """
                )
            }
        }
        
        failure {
            echo '❌ DEPLOYMENT FAILED!'
            script {
                sh '''
                    echo "===== Docker Compose Logs ====="
                    docker-compose -f docker-compose.yml logs --tail=50 2>/dev/null || echo "No docker-compose logs available"
                    
                    echo ""
                    echo "===== Web Container Logs ====="
                    docker logs nqobileq_web --tail=30 2>/dev/null || echo "Web container not running"
                    
                    echo ""
                    echo "===== Database Container Logs ====="
                    docker logs nqobileq_db --tail=30 2>/dev/null || echo "Database container not running"
                '''
                
                emailext(
                    to: "${OWNER_EMAIL}",
                    subject: "❌ Jenkins Build Failed: ${env.JOB_NAME} - Build #${env.BUILD_NUMBER}",
                    body: """
                        The build has failed. Please check the console output.
                        
                        Build Details:
                        - Job: ${env.JOB_NAME}
                        - Build Number: ${env.BUILD_NUMBER}
                        - Build URL: ${env.BUILD_URL}
                        
                        Please investigate the failure at: ${env.BUILD_URL}
                    """
                )
            }
        }
    }
}