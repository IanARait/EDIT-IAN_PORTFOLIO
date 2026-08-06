<?php
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/classes/Database.php';
require_once dirname(__DIR__) . '/classes/Auth.php';
require_once dirname(__DIR__) . '/classes/Project.php';
require_once dirname(__DIR__) . '/classes/Message.php';
require_once dirname(__DIR__) . '/classes/Setting.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized. Please log in.']);
    exit;
}

$action = $_POST['action'] ?? '';

try {
    switch ($action) {

        case 'save_project':
            $projectModel = new Project();
            $projectId = !empty($_POST['project_id']) ? (int)$_POST['project_id'] : null;

            $data = [
                'title'         => sanitize($_POST['title'] ?? ''),
                'client'        => sanitize($_POST['client'] ?? ''),
                'category_id'   => (int)($_POST['category_id'] ?? 0),
                'description'   => $_POST['description'] ?? '',
                'video_url'     => sanitize($_POST['video_url'] ?? ''),
                'github_url'    => sanitize($_POST['github_url'] ?? ''),
                'year'          => sanitize($_POST['year'] ?? date('Y')),
                'duration'      => sanitize($_POST['duration'] ?? ''),
                'software_used' => sanitize($_POST['software_used'] ?? ''),
                'featured'      => isset($_POST['featured']) ? 1 : 0,
                'status'        => in_array(($_POST['status'] ?? ''), ['published', 'draft']) ? $_POST['status'] : 'draft',
                'updated_at'    => date('Y-m-d H:i:s'),
            ];

            if (!empty($_FILES['thumbnail']['tmp_name'])) {
                $uploadResult = uploadFile($_FILES['thumbnail'], THUMBNAILS_PATH, ALLOWED_IMAGE_TYPES);
                if ($uploadResult['success']) {
                    if ($projectId) {
                        $existing = $projectModel->getById($projectId);
                        if ($existing && !empty($existing['thumbnail'])) {
                            $oldPath = THUMBNAILS_PATH . '/' . $existing['thumbnail'];
                            if (file_exists($oldPath)) unlink($oldPath);
                        }
                    }
                    $data['thumbnail'] = $uploadResult['filename'];
                } else {
                    echo json_encode(['success' => false, 'message' => 'Thumbnail upload failed: ' . $uploadResult['error']]);
                    exit;
                }
            }

            if ($projectId) {
                $projectModel->update($projectId, $data);
                echo json_encode(['success' => true, 'message' => 'Project updated successfully.', 'project_id' => $projectId]);
            } else {
                $data['created_at'] = date('Y-m-d H:i:s');
                $data['views'] = 0;
                $data['sort_order'] = 0;
                $newId = $projectModel->create($data);
                echo json_encode(['success' => true, 'message' => 'Project created successfully.', 'project_id' => $newId]);
            }
            break;

        case 'delete_project':
            $projectModel = new Project();
            $projectId = (int)($_POST['project_id'] ?? $_POST['id'] ?? 0);

            if (!$projectId) {
                echo json_encode(['success' => false, 'message' => 'Invalid project ID.']);
                exit;
            }

            $projectModel->delete($projectId);
            echo json_encode(['success' => true, 'message' => 'Project deleted successfully.']);
            break;

        case 'toggle_featured':
            $db = Database::getInstance();
            $projectId = (int)($_POST['project_id'] ?? $_POST['id'] ?? 0);

            if (!$projectId) {
                echo json_encode(['success' => false, 'message' => 'Invalid project ID.']);
                exit;
            }

            $project = $db->selectOne("SELECT featured FROM projects WHERE id = ?", [$projectId]);
            if (!$project) {
                echo json_encode(['success' => false, 'message' => 'Project not found.']);
                exit;
            }

            $newFeatured = $project['featured'] ? 0 : 1;
            $db->update('projects', ['featured' => $newFeatured, 'updated_at' => date('Y-m-d H:i:s')], 'id = ?', [$projectId]);
            echo json_encode(['success' => true, 'featured' => $newFeatured, 'message' => $newFeatured ? 'Project marked as featured.' : 'Project removed from featured.']);
            break;

        case 'toggle_status':
            $db = Database::getInstance();
            $projectId = (int)($_POST['project_id'] ?? $_POST['id'] ?? 0);
            $newStatus = $_POST['status'] ?? '';

            if (!$projectId || !in_array($newStatus, ['published', 'draft'])) {
                echo json_encode(['success' => false, 'message' => 'Invalid parameters.']);
                exit;
            }

            $db->update('projects', ['status' => $newStatus, 'updated_at' => date('Y-m-d H:i:s')], 'id = ?', [$projectId]);
            echo json_encode(['success' => true, 'status' => $newStatus, 'message' => 'Project status updated to ' . ucfirst($newStatus) . '.']);
            break;

        case 'delete_message':
            $messageModel = new Message();
            $messageId = (int)($_POST['message_id'] ?? $_POST['id'] ?? 0);

            if (!$messageId) {
                echo json_encode(['success' => false, 'message' => 'Invalid message ID.']);
                exit;
            }

            $messageModel->delete($messageId);
            echo json_encode(['success' => true, 'message' => 'Message deleted successfully.']);
            break;

        case 'toggle_read':
            $messageModel = new Message();
            $messageId = (int)($_POST['message_id'] ?? $_POST['id'] ?? 0);

            if (!$messageId) {
                echo json_encode(['success' => false, 'message' => 'Invalid message ID.']);
                exit;
            }

            $msg = $messageModel->getById($messageId);
            if (!$msg) {
                echo json_encode(['success' => false, 'message' => 'Message not found.']);
                exit;
            }

            $newRead = $msg['is_read'] ? 0 : 1;
            if ($newRead) {
                $messageModel->markAsRead($messageId);
            } else {
                $messageModel->markAsUnread($messageId);
            }

            echo json_encode(['success' => true, 'is_read' => $newRead, 'message' => $newRead ? 'Message marked as read.' : 'Message marked as unread.']);
            break;

        case 'bulk_delete_messages':
            $messageModel = new Message();
            $filter = $_POST['filter'] ?? '';

            if ($filter === 'read') {
                $deleted = $messageModel->deleteRead();
                echo json_encode(['success' => true, 'message' => $deleted . ' read message(s) deleted.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid filter.']);
            }
            break;

        case 'save_settings':
            $settingModel = new Setting();
            $settingsData = [];

            $allowedKeys = [
                'site_name', 'site_tagline', 'site_description', 'site_logo',
                'hero_title', 'hero_subtitle', 'cta_primary_text', 'cta_secondary_text',
                'about_text', 'experience_years', 'total_projects', 'total_clients', 'videos_edited',
                'contact_email', 'contact_phone', 'contact_location',
                'social_youtube', 'social_instagram', 'social_twitter', 'social_linkedin', 'social_tiktok',
                'smtp_host', 'smtp_port', 'smtp_username', 'smtp_password', 'smtp_encryption',
                'footer_text', 'resume_file',
            ];

            foreach ($_POST as $key => $value) {
                if ($key === 'action' || $key === CSRF_TOKEN_NAME) continue;
                if (in_array($key, $allowedKeys)) {
                    $settingsData[$key] = $key === 'smtp_password' ? $value : sanitize($value);
                }
            }

            if (!empty($_FILES['site_logo']['tmp_name'])) {
                $logoUpload = uploadFile($_FILES['site_logo'], LOGOS_PATH, ALLOWED_IMAGE_TYPES);
                if ($logoUpload['success']) {
                    $oldLogo = $settingModel->get('site_logo');
                    if ($oldLogo) {
                        $oldPath = LOGOS_PATH . '/' . $oldLogo;
                        if (file_exists($oldPath)) unlink($oldPath);
                    }
                    $settingsData['site_logo'] = $logoUpload['filename'];
                } else {
                    echo json_encode(['success' => false, 'message' => 'Logo upload failed: ' . $logoUpload['error']]);
                    exit;
                }
            }

            if (!empty($_FILES['resume_file']['tmp_name'])) {
                $resumeUpload = uploadFile($_FILES['resume_file'], UPLOADS_PATH, ALLOWED_DOC_TYPES);
                if ($resumeUpload['success']) {
                    $oldResume = $settingModel->get('resume_file');
                    if ($oldResume) {
                        $oldPath = UPLOADS_PATH . '/' . $oldResume;
                        if (file_exists($oldPath)) unlink($oldPath);
                    }
                    $settingsData['resume_file'] = $resumeUpload['filename'];
                } else {
                    echo json_encode(['success' => false, 'message' => 'Resume upload failed: ' . $resumeUpload['error']]);
                    exit;
                }
            }

            if (!empty($settingsData)) {
                $settingModel->bulkUpdate($settingsData);
            }

            echo json_encode(['success' => true, 'message' => 'Settings saved successfully.']);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Unknown action: ' . $action]);
            break;
    }
} catch (Exception $e) {
    error_log('AJAX Error [' . $action . ']: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An internal error occurred. Please try again.']);
}
