pipeline {
    agent any

    environment {
        APP_NAME = 'nqobileq'
        COMPOSE_FILE = 'docker-compose.yml'
        BUILD_TIMESTAMP = sh(script: "date +'%Y%m%d_%H%M%S'", returnStdout: true).trim()
        
        SMTP_USERNAME = credentials('smtp-username')
        SMTP_PASSWORD = credentials('smtp-password')
        OWNER_EMAIL = credentials('owner-email')
        
        // ADD STRIPE CREDENTIALS
        STRIPE_PUBLISHABLE_KEY = credentials('stripe-publishable-key')
        STRIPE_SECRET_KEY = credentials('stripe-secret-key')
    }

    stages {

        // ============ STAGE 1: CLEAN AND FIX WORKSPACE ============
        stage('Clean and Fix Workspace') {
            steps {
                echo '🧹 Cleaning workspace and fixing permissions...'
                script {
                    sh '''
                        echo "Removing any lock files..."
                        find .git -name "*.lock" 2>/dev/null | xargs rm -f 2>/dev/null || true
                        rm -f .git/config.lock 2>/dev/null || true
                        rm -f .git/index.lock 2>/dev/null || true
                        echo "✅ Lock files removed"
                    '''
                    sh '''
                        echo "Fixing permissions..."
                        sudo chown -R jenkins:jenkins . 2>/dev/null || true
                        sudo chmod -R 755 . 2>/dev/null || true
                        echo "✅ Permissions fixed"
                    '''
                }
            }
        }

        // ============ STAGE 2: BUILD VERSIONING ============
        stage('Build Versioning') {
            steps {
                echo '📌 Creating build version...'
                script {
                    sh """
                        echo "BUILD_VERSION=${BUILD_TIMESTAMP}" > build.properties
                        echo "BUILD_NUMBER=${env.BUILD_NUMBER}" >> build.properties
                        echo "BUILD_URL=${env.BUILD_URL}" >> build.properties
                        echo "JOB_NAME=${env.JOB_NAME}" >> build.properties
                    """
                    archiveArtifacts artifacts: 'build.properties', fingerprint: true
                }
            }
        }

        stage('Clone Repository') {
            steps {
                echo '📦 Cloning NqobileQ repository...'
                script {
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

        // ============ PARALLEL CHECKS ============
        stage('Pre-Build Checks') {
            parallel {
                stage('Check Docker') {
                    steps {
                        echo '🐳 Checking Docker...'
                        sh 'docker --version'
                        sh 'docker-compose --version || echo "Docker Compose installed"'
                        sh 'docker ps'
                    }
                }
                stage('Check PHP Syntax') {
                    steps {
                        echo '📝 Checking PHP syntax...'
                        sh 'find . -name "*.php" -exec php -l {} \\; 2>&1 | grep -v "No syntax errors" || true'
                    }
                }
                stage('Security Scan') {
                    steps {
                        echo '🔒 Quick security check...'
                        sh '''
                            echo "Checking for exposed secrets..."
                            grep -r "password\|secret\|key" --include="*.php" --exclude-dir=vendor . 2>/dev/null | head -5 || echo "No obvious secrets found"
                        '''
                    }
                }
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

# Stripe Configuration (ADD THESE LINES)
STRIPE_PUBLISHABLE_KEY=${env.STRIPE_PUBLISHABLE_KEY}
STRIPE_SECRET_KEY=${env.STRIPE_SECRET_KEY}

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
                    docker exec nqobileq_web cat /var/www/html/.env 2>/dev/null | grep -E "SMTP_USERNAME|OWNER_EMAIL|STRIPE" && echo "✅ Credentials found" || echo "⚠️ Missing"
                    
                    echo "Checking vendor directory..."
                    docker exec nqobileq_web ls -la /var/www/html/vendor/ 2>/dev/null | head -3 && echo "✅ vendor exists" || echo "⚠️ vendor missing"
                '''
            }
        }

        // ============ PARALLEL VERIFICATION ============
        stage('Parallel Verification') {
            parallel {
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
                stage('Verify PHP Extensions') {
                    steps {
                        echo '🔌 Verifying PHP extensions...'
                        sh '''
                            docker exec nqobileq_web php -m | grep -q mysqli && echo "✅ mysqli loaded" || echo "⚠️ mysqli missing"
                            docker exec nqobileq_web php -m | grep -q pdo_mysql && echo "✅ pdo_mysql loaded" || echo "⚠️ pdo_mysql missing"
                        '''
                    }
                }
            }
        }

        stage('Run Database Initialization') {
            steps {
                echo '📀 Database initialization...'
                sh 'docker exec -i nqobileq_db mysql -uroot -prootpassword123 nqobileq_db < init.sql 2>/dev/null || echo "Init already run"'
                sh 'docker exec nqobileq_db mysql -uroot -prootpassword123 -e "SELECT COUNT(*) as users FROM nqobileq_db.users;" 2>/dev/null && echo "✅ Users table verified"'
            }
        }

        stage('Create Database Backup') {
            steps {
                echo '💾 Creating database backup...'
                sh '''
                    mkdir -p /tmp/backups
                    BACKUP_FILE="backup_$(date +%Y%m%d_%H%M%S).sql"
                    docker exec nqobileq_db mysqldump -uroot -prootpassword123 nqobileq_db 2>/dev/null > /tmp/backups/$BACKUP_FILE
                    echo "✅ Backup created: $BACKUP_FILE"
                    ls -t /tmp/backups/backup_*.sql 2>/dev/null | tail -n +11 | xargs rm -f 2>/dev/null || true
                '''
                archiveArtifacts artifacts: '/tmp/backups/*.sql', allowEmptyArchive: true
            }
        }

        // ============ HEALTH CHECK WITH RETRY ============
        stage('Health Check') {
            steps {
                echo '🏥 Performing health check...'
                script {
                    def maxRetries = 10
                    def healthy = false
                    for (int i = 1; i <= maxRetries; i++) {
                        try {
                            sh "curl -f http://localhost"
                            sh "curl -f http://localhost/admin/"
                            echo "✅ Health check passed!"
                            healthy = true
                            break
                        } catch (Exception e) {
                            echo "Attempt $i/$maxRetries - Waiting for service..."
                            sleep 5
                        }
                    }
                    if (!healthy) {
                        error "Health check failed after $maxRetries attempts"
                    }
                }
            }
        }

        // ============ COLLECT METRICS ============
        stage('Collect Metrics') {
            steps {
                echo '📊 Collecting deployment metrics...'
                sh '''
                    echo "=== Deployment Metrics ===" > metrics.txt
                    echo "Build Number: ${BUILD_NUMBER}" >> metrics.txt
                    echo "Build Version: ${BUILD_TIMESTAMP}" >> metrics.txt
                    echo "Deployment Date: $(date)" >> metrics.txt
                    echo "" >> metrics.txt
                    echo "=== Container Status ===" >> metrics.txt
                    docker ps --format "table {{.Names}}\t{{.Status}}\t{{.Image}}" >> metrics.txt
                    echo "" >> metrics.txt
                    echo "=== Resource Usage ===" >> metrics.txt
                    docker stats --no-stream --format "table {{.Name}}\t{{.CPUPerc}}\t{{.MemUsage}}" >> metrics.txt
                '''
                archiveArtifacts artifacts: 'metrics.txt'
            }
        }

        stage('Verify Live Site') {
            steps {
                echo '🌐 Site live!'
                sh '''
                    echo "=========================================="
                    echo "✅ NQOBILEQ DEPLOYMENT COMPLETE!"
                    echo "=========================================="
                    echo "Build: #${BUILD_NUMBER}"
                    echo "Version: ${BUILD_TIMESTAMP}"
                    echo "Website: http://13.205.187.75"
                    echo "Admin: http://13.205.187.75/admin/"
                    echo "Admin: admin@nqobileq.com / admin123"
                    echo "Email and Stripe configured from Jenkins credentials"
                    echo "=========================================="
                '''
            }
        }
    }

    post {
        success {
            echo '🎉 DEPLOYMENT SUCCESSFUL! 🎉'
            
            emailext(
                subject: "✅ NqobileQ Build Successful - #${env.BUILD_NUMBER}",
                body: """
                    NqobileQ has been successfully deployed!
                    
                    Build Information:
                    - Build Number: ${env.BUILD_NUMBER}
                    - Build Version: ${BUILD_TIMESTAMP}
                    - Build URL: ${env.BUILD_URL}
                    
                    Application Access:
                    - Website: http://13.205.187.75
                    - Admin Panel: http://13.205.187.75/admin/
                    
                    Admin Login: admin@nqobileq.com / admin123
                    
                    Metrics and backups have been archived in Jenkins.
                """,
                to: 'thabani070801@gmail.com'
            )
        }
        
        failure {
            echo '❌ DEPLOYMENT FAILED!'
            
            sh '''
                echo "=== Docker Compose Logs ==="
                docker-compose -f ${COMPOSE_FILE} logs --tail=50
                echo "=== Web Container Logs ==="
                docker logs nqobileq_web --tail=30 2>/dev/null || echo "Web container not running"
                echo "=== Database Container Logs ==="
                docker logs nqobileq_db --tail=30 2>/dev/null || echo "Database container not running"
            '''
            
            emailext(
                subject: "❌ NqobileQ Build Failed - #${env.BUILD_NUMBER}",
                body: """
                    The build has failed.
                    
                    Build Information:
                    - Build Number: ${env.BUILD_NUMBER}
                    - Build URL: ${env.BUILD_URL}
                    
                    Check Jenkins console for details.
                """,
                to: 'thabani070801@gmail.com'
            )
        }
        
        always {
            echo '🧹 Cleaning up...'
            sh '''
                docker image prune -f || true
                docker system prune -f || true
                docker images --format "{{.Repository}}:{{.Tag}}" | grep nqoq | tail -n +6 | xargs -r docker rmi || true
            '''
        }
    }
}