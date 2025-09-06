<?php

namespace App\Constants;

class ErrorCodes
{
    // Authentication & Authorization Errors
    public const UNAUTHENTICATED = 'UNAUTHENTICATED';
    public const UNAUTHENTICATED_MSG = 'User not authenticated';
    public const TOKEN_EXPIRED = 'TOKEN_EXPIRED';
    public const TOKEN_EXPIRED_MSG = 'Token has expired';
    public const TOKEN_INVALID = 'TOKEN_INVALID';
    public const TOKEN_INVALID_MSG = 'Invalid token';
    public const TOKEN_ABSENT = 'TOKEN_ABSENT';
    public const TOKEN_ABSENT_MSG = 'Token is missing';
    public const TOKEN_BLACKLISTED = 'TOKEN_BLACKLISTED';
    public const TOKEN_BLACKLISTED_MSG = 'Token has been blacklisted';
    public const FORBIDDEN = 'FORBIDDEN';
    public const FORBIDDEN_MSG = 'Access forbidden';
    public const ACCESS_DENIED = 'ACCESS_DENIED';
    public const ACCESS_DENIED_MSG = 'Access denied';
    public const INSUFFICIENT_PERMISSIONS = 'INSUFFICIENT_PERMISSIONS';
    public const INSUFFICIENT_PERMISSIONS_MSG = 'Insufficient permissions';
    public const INVALID_CREDENTIALS = 'INVALID_CREDENTIALS';
    public const INVALID_CREDENTIALS_MSG = 'Invalid credentials';
    public const ACCOUNT_DISABLED = 'ACCOUNT_DISABLED';
    public const ACCOUNT_DISABLED_MSG = 'Account has been disabled';
    public const ACCOUNT_NOT_VERIFIED = 'ACCOUNT_NOT_VERIFIED';
    public const ACCOUNT_NOT_VERIFIED_MSG = 'Account not verified';
    public const LOGIN_ATTEMPTS_EXCEEDED = 'LOGIN_ATTEMPTS_EXCEEDED';
    public const LOGIN_ATTEMPTS_EXCEEDED_MSG = 'Maximum login attempts exceeded';

    // Validation Errors
    public const VALIDATION_ERROR = 'VALIDATION_ERROR';
    public const VALIDATION_ERROR_MSG = 'Validation error';
    public const INVALID_INPUT = 'INVALID_INPUT';
    public const INVALID_INPUT_MSG = 'Invalid input';
    public const MISSING_REQUIRED_FIELD = 'MISSING_REQUIRED_FIELD';
    public const MISSING_REQUIRED_FIELD_MSG = 'Required field is missing';
    public const INVALID_EMAIL_FORMAT = 'INVALID_EMAIL_FORMAT';
    public const INVALID_EMAIL_FORMAT_MSG = 'Invalid email format';
    public const INVALID_PASSWORD_FORMAT = 'INVALID_PASSWORD_FORMAT';
    public const INVALID_PASSWORD_FORMAT_MSG = 'Invalid password format';
    public const PASSWORDS_DO_NOT_MATCH = 'PASSWORDS_DO_NOT_MATCH';
    public const PASSWORDS_DO_NOT_MATCH_MSG = 'Passwords do not match';
    public const INVALID_DATE_FORMAT = 'INVALID_DATE_FORMAT';
    public const INVALID_DATE_FORMAT_MSG = 'Invalid date format';
    public const INVALID_TIME_FORMAT = 'INVALID_TIME_FORMAT';
    public const INVALID_TIME_FORMAT_MSG = 'Invalid time format';
    public const INVALID_JSON_FORMAT = 'INVALID_JSON_FORMAT';
    public const INVALID_JSON_FORMAT_MSG = 'Invalid JSON format';
    public const VALUE_TOO_LONG = 'VALUE_TOO_LONG';
    public const VALUE_TOO_LONG_MSG = 'Value is too long';
    public const VALUE_TOO_SHORT = 'VALUE_TOO_SHORT';
    public const VALUE_TOO_SHORT_MSG = 'Value is too short';
    public const INVALID_NUMERIC_VALUE = 'INVALID_NUMERIC_VALUE';
    public const INVALID_NUMERIC_VALUE_MSG = 'Invalid numeric value';

    // Resource Not Found Errors
    public const NOT_FOUND = 'NOT_FOUND';
    public const NOT_FOUND_MSG = 'Not found';
    public const RESOURCE_NOT_FOUND = 'RESOURCE_NOT_FOUND';
    public const RESOURCE_NOT_FOUND_MSG = 'Resource not found';
    public const USER_NOT_FOUND = 'USER_NOT_FOUND';
    public const USER_NOT_FOUND_MSG = 'User not found';
    public const TEST_NOT_FOUND = 'TEST_NOT_FOUND';
    public const TEST_NOT_FOUND_MSG = 'Test not found';
    public const QUESTION_NOT_FOUND = 'QUESTION_NOT_FOUND';
    public const QUESTION_NOT_FOUND_MSG = 'Question not found';
    public const CATEGORY_NOT_FOUND = 'CATEGORY_NOT_FOUND';
    public const CATEGORY_NOT_FOUND_MSG = 'Category not found';
    public const ATTEMPT_NOT_FOUND = 'ATTEMPT_NOT_FOUND';
    public const ATTEMPT_NOT_FOUND_MSG = 'Attempt not found';
    public const ROUTE_NOT_FOUND = 'ROUTE_NOT_FOUND';
    public const ROUTE_NOT_FOUND_MSG = 'Route not found';

    // User Management Errors
    public const USER_ALREADY_EXISTS = 'USER_ALREADY_EXISTS';
    public const USER_ALREADY_EXISTS_MSG = 'User already exists';
    public const EMAIL_ALREADY_EXISTS = 'EMAIL_ALREADY_EXISTS';
    public const EMAIL_ALREADY_EXISTS_MSG = 'Email already exists';
    public const USER_ALREADY_ACTIVE = 'USER_ALREADY_ACTIVE';
    public const USER_ALREADY_ACTIVE_MSG = 'User is already active';
    public const USER_ALREADY_INACTIVE = 'USER_ALREADY_INACTIVE';
    public const USER_ALREADY_INACTIVE_MSG = 'User is already inactive';
    public const USER_ALREADY_TEACHER = 'USER_ALREADY_TEACHER';
    public const USER_ALREADY_TEACHER_MSG = 'User is already a teacher';
    public const USER_ALREADY_ADMIN = 'USER_ALREADY_ADMIN';
    public const USER_ALREADY_ADMIN_MSG = 'User is already an admin';
    public const CANNOT_DELETE_YOURSELF = 'CANNOT_DELETE_YOURSELF';
    public const CANNOT_DELETE_YOURSELF_MSG = 'Cannot delete yourself';
    public const CANNOT_DEACTIVATE_YOURSELF = 'CANNOT_DEACTIVATE_YOURSELF';
    public const CANNOT_DEACTIVATE_YOURSELF_MSG = 'Cannot deactivate yourself';
    public const CANNOT_DEMOTE_YOURSELF = 'CANNOT_DEMOTE_YOURSELF';
    public const CANNOT_DEMOTE_YOURSELF_MSG = 'Cannot demote yourself';
    public const USER_HAS_DEPENDENCIES = 'USER_HAS_DEPENDENCIES';
    public const USER_HAS_DEPENDENCIES_MSG = 'User has dependencies and cannot be deleted';

    // Category Management Errors
    public const CATEGORY_ALREADY_EXISTS = 'CATEGORY_ALREADY_EXISTS';
    public const CATEGORY_ALREADY_EXISTS_MSG = 'Category already exists';
    public const CATEGORY_IN_USE = 'CATEGORY_IN_USE';
    public const CATEGORY_IN_USE_MSG = 'Category is in use';
    public const CATEGORY_REQUIRED = 'CATEGORY_REQUIRED';
    public const CATEGORY_REQUIRED_MSG = 'Category is required';
    public const INVALID_CATEGORY = 'INVALID_CATEGORY';
    public const INVALID_CATEGORY_MSG = 'Invalid category';
    public const CATEGORY_NOT_ACTIVE = 'CATEGORY_NOT_ACTIVE';
    public const CATEGORY_NOT_ACTIVE_MSG = 'Category is not active';
    public const CANNOT_DELETE_CATEGORY_WITH_TESTS = 'CANNOT_DELETE_CATEGORY_WITH_TESTS';
    public const CANNOT_DELETE_CATEGORY_WITH_TESTS_MSG = 'Cannot delete category with tests';
    public const CATEGORY_NAME_TOO_LONG = 'CATEGORY_NAME_TOO_LONG';
    public const CATEGORY_NAME_TOO_LONG_MSG = 'Category name is too long';

    // Test Management Errors
    public const TEST_ALREADY_EXISTS = 'TEST_ALREADY_EXISTS';
    public const TEST_ALREADY_EXISTS_MSG = 'Test already exists';
    public const TEST_NOT_ACTIVE = 'TEST_NOT_ACTIVE';
    public const TEST_NOT_ACTIVE_MSG = 'Test is not active';
    public const TEST_NOT_PUBLIC = 'TEST_NOT_PUBLIC';
    public const TEST_NOT_PUBLIC_MSG = 'Test is not public';
    public const TEST_ALREADY_PUBLISHED = 'TEST_ALREADY_PUBLISHED';
    public const TEST_ALREADY_PUBLISHED_MSG = 'Test is already published';
    public const TEST_NOT_PUBLISHED = 'TEST_NOT_PUBLISHED';
    public const TEST_NOT_PUBLISHED_MSG = 'Test is not published';
    public const CANNOT_MODIFY_PUBLISHED_TEST = 'CANNOT_MODIFY_PUBLISHED_TEST';
    public const CANNOT_MODIFY_PUBLISHED_TEST_MSG = 'Cannot modify published test';
    public const CANNOT_DELETE_TEST_WITH_ATTEMPTS = 'CANNOT_DELETE_TEST_WITH_ATTEMPTS';
    public const CANNOT_DELETE_TEST_WITH_ATTEMPTS_MSG = 'Cannot delete test with attempts';
    public const CANNOT_DELETE_OTHER_USER_TEST = 'CANNOT_DELETE_OTHER_USER_TEST';
    public const CANNOT_DELETE_OTHER_USER_TEST_MSG = 'Cannot delete other user\'s test';
    public const CANNOT_DELETE_PUBLISHED_TEST = 'CANNOT_DELETE_PUBLISHED_TEST';
    public const CANNOT_DELETE_PUBLISHED_TEST_MSG = 'Cannot delete published test';
    public const TEST_HAS_NO_QUESTIONS = 'TEST_HAS_NO_QUESTIONS';
    public const TEST_HAS_NO_QUESTIONS_MSG = 'Test has no questions';
    public const TEST_MINIMUM_QUESTIONS_REQUIRED = 'TEST_MINIMUM_QUESTIONS_REQUIRED';
    public const TEST_MINIMUM_QUESTIONS_REQUIRED_MSG = 'Test minimum questions required';
    public const INVALID_TEST_DURATION = 'INVALID_TEST_DURATION';
    public const INVALID_TEST_DURATION_MSG = 'Invalid test duration';
    public const TEST_DURATION_TOO_SHORT = 'TEST_DURATION_TOO_SHORT';
    public const TEST_DURATION_TOO_SHORT_MSG = 'Test duration is too short';
    public const TEST_DURATION_TOO_LONG = 'TEST_DURATION_TOO_LONG';
    public const TEST_DURATION_TOO_LONG_MSG = 'Test duration is too long';
    public const NOT_TEST_OWNER = 'NOT_TEST_OWNER';
    public const NOT_TEST_OWNER_MSG = 'Not test owner';

    // Question Management Errors
    public const QUESTION_ALREADY_EXISTS = 'QUESTION_ALREADY_EXISTS';
    public const QUESTION_ALREADY_EXISTS_MSG = 'Question already exists';
    public const INVALID_QUESTION_TYPE = 'INVALID_QUESTION_TYPE';
    public const INVALID_QUESTION_TYPE_MSG = 'Invalid question type';
    public const QUESTION_TEXT_REQUIRED = 'QUESTION_TEXT_REQUIRED';
    public const QUESTION_TEXT_REQUIRED_MSG = 'Question text is required';
    public const CORRECT_ANSWER_REQUIRED = 'CORRECT_ANSWER_REQUIRED';
    public const CORRECT_ANSWER_REQUIRED_MSG = 'Correct answer is required';
    public const INVALID_CORRECT_ANSWER = 'INVALID_CORRECT_ANSWER';
    public const INVALID_CORRECT_ANSWER_MSG = 'Invalid correct answer';
    public const OPTIONS_REQUIRED = 'OPTIONS_REQUIRED';
    public const OPTIONS_REQUIRED_MSG = 'Options are required';
    public const INSUFFICIENT_OPTIONS = 'INSUFFICIENT_OPTIONS';
    public const INSUFFICIENT_OPTIONS_MSG = 'Insufficient options provided';
    public const TOO_MANY_OPTIONS = 'TOO_MANY_OPTIONS';
    public const TOO_MANY_OPTIONS_MSG = 'Too many options provided';
    public const DUPLICATE_OPTIONS = 'DUPLICATE_OPTIONS';
    public const DUPLICATE_OPTIONS_MSG = 'Duplicate options found';
    public const INVALID_POINTS_VALUE = 'INVALID_POINTS_VALUE';
    public const INVALID_POINTS_VALUE_MSG = 'Invalid points value';
    public const NEGATIVE_POINTS_NOT_ALLOWED = 'NEGATIVE_POINTS_NOT_ALLOWED';
    public const NEGATIVE_POINTS_NOT_ALLOWED_MSG = 'Negative points not allowed';
    public const QUESTION_ORDER_INVALID = 'QUESTION_ORDER_INVALID';
    public const QUESTION_ORDER_INVALID_MSG = 'Question order is invalid';
    public const CANNOT_DELETE_QUESTION_WITH_ATTEMPTS = 'CANNOT_DELETE_QUESTION_WITH_ATTEMPTS';
    public const CANNOT_DELETE_QUESTION_WITH_ATTEMPTS_MSG = 'Cannot delete question with attempts';

    // Test Attempt Errors
    public const TEST_ALREADY_STARTED = 'TEST_ALREADY_STARTED';
    public const TEST_ALREADY_STARTED_MSG = 'Test already started';
    public const TEST_ALREADY_COMPLETED = 'TEST_ALREADY_COMPLETED';
    public const TEST_ALREADY_COMPLETED_MSG = 'Test already completed';
    public const TEST_NOT_STARTED = 'TEST_NOT_STARTED';
    public const TEST_NOT_STARTED_MSG = 'Test not started';
    public const TEST_TIME_EXPIRED = 'TEST_TIME_EXPIRED';
    public const TEST_TIME_EXPIRED_MSG = 'Test time has expired';
    public const ATTEMPT_NOT_IN_PROGRESS = 'ATTEMPT_NOT_IN_PROGRESS';
    public const ATTEMPT_NOT_IN_PROGRESS_MSG = 'Attempt not in progress';
    public const ATTEMPT_ALREADY_SUBMITTED = 'ATTEMPT_ALREADY_SUBMITTED';
    public const ATTEMPT_ALREADY_SUBMITTED_MSG = 'Attempt already submitted';
    public const INVALID_ATTEMPT_STATUS = 'INVALID_ATTEMPT_STATUS';
    public const INVALID_ATTEMPT_STATUS_MSG = 'Invalid attempt status';
    public const CANNOT_ATTEMPT_OWN_TEST = 'CANNOT_ATTEMPT_OWN_TEST';
    public const CANNOT_ATTEMPT_OWN_TEST_MSG = 'Cannot attempt own test';
    public const MAXIMUM_ATTEMPTS_EXCEEDED = 'MAXIMUM_ATTEMPTS_EXCEEDED';
    public const MAXIMUM_ATTEMPTS_EXCEEDED_MSG = 'Maximum attempts exceeded';
    public const ATTEMPT_NOT_ALLOWED = 'ATTEMPT_NOT_ALLOWED';
    public const ATTEMPT_NOT_ALLOWED_MSG = 'Attempt not allowed';
    public const INVALID_ANSWER_FORMAT = 'INVALID_ANSWER_FORMAT';
    public const INVALID_ANSWER_FORMAT_MSG = 'Invalid answer format';
    public const ANSWER_REQUIRED = 'ANSWER_REQUIRED';
    public const ANSWER_REQUIRED_MSG = 'Answer is required';
    public const INVALID_QUESTION_ID = 'INVALID_QUESTION_ID';
    public const INVALID_QUESTION_ID_MSG = 'Invalid question ID';
    public const QUESTION_NOT_IN_TEST = 'QUESTION_NOT_IN_TEST';
    public const QUESTION_NOT_IN_TEST_MSG = 'Question not in test';
    public const CANNOT_EDIT_OTHER_USER_TEST = 'CANNOT_EDIT_OTHER_USER_TEST';
    public const CANNOT_EDIT_OTHER_USER_TEST_MSG = 'Cannot edit other user\'s test';

    // System & Server Errors
    public const INTERNAL_SERVER_ERROR = 'INTERNAL_SERVER_ERROR';
    public const INTERNAL_SERVER_ERROR_MSG = 'Internal server error';
    public const DATABASE_ERROR = 'DATABASE_ERROR';
    public const DATABASE_ERROR_MSG = 'Database error';
    public const DATABASE_CONNECTION_FAILED = 'DATABASE_CONNECTION_FAILED';
    public const DATABASE_CONNECTION_FAILED_MSG = 'Database connection failed';
    public const QUERY_TIMEOUT = 'QUERY_TIMEOUT';
    public const QUERY_TIMEOUT_MSG = 'Query timeout';
    public const EXTERNAL_API_ERROR = 'EXTERNAL_API_ERROR';
    public const EXTERNAL_API_ERROR_MSG = 'External API error';
    public const SERVICE_UNAVAILABLE = 'SERVICE_UNAVAILABLE';
    public const SERVICE_UNAVAILABLE_MSG = 'Service unavailable';
    public const MAINTENANCE_MODE = 'MAINTENANCE_MODE';
    public const MAINTENANCE_MODE_MSG = 'System is in maintenance mode';
    public const CONFIGURATION_ERROR = 'CONFIGURATION_ERROR';
    public const CONFIGURATION_ERROR_MSG = 'Configuration error';

    // Rate Limiting & Security
    public const RATE_LIMIT_EXCEEDED = 'RATE_LIMIT_EXCEEDED';
    public const RATE_LIMIT_EXCEEDED_MSG = 'Rate limit exceeded';
    public const TOO_MANY_REQUESTS = 'TOO_MANY_REQUESTS';
    public const TOO_MANY_REQUESTS_MSG = 'Too many requests';
    public const SUSPICIOUS_ACTIVITY = 'SUSPICIOUS_ACTIVITY';
    public const SUSPICIOUS_ACTIVITY_MSG = 'Suspicious activity detected';
    public const IP_BLOCKED = 'IP_BLOCKED';
    public const IP_BLOCKED_MSG = 'IP address blocked';
    public const SECURITY_VIOLATION = 'SECURITY_VIOLATION';
    public const SECURITY_VIOLATION_MSG = 'Security violation';
    public const INVALID_REQUEST_METHOD = 'INVALID_REQUEST_METHOD';
    public const INVALID_REQUEST_METHOD_MSG = 'Invalid request method';

    // File Operation Errors
    public const FILE_NOT_FOUND = 'FILE_NOT_FOUND';
    public const FILE_NOT_FOUND_MSG = 'File not found';
    public const FILE_UPLOAD_ERROR = 'FILE_UPLOAD_ERROR';
    public const FILE_UPLOAD_ERROR_MSG = 'File upload error';
    public const FILE_UPLOAD_FAILED = 'FILE_UPLOAD_FAILED';
    public const FILE_UPLOAD_FAILED_MSG = 'File upload failed';
    public const INVALID_FILE_TYPE = 'INVALID_FILE_TYPE';
    public const INVALID_FILE_TYPE_MSG = 'Invalid file type';
    public const FILE_TOO_LARGE = 'FILE_TOO_LARGE';
    public const FILE_TOO_LARGE_MSG = 'File is too large';
    public const FILE_TOO_SMALL = 'FILE_TOO_SMALL';
    public const FILE_TOO_SMALL_MSG = 'File is too small';
    public const FILE_CORRUPTED = 'FILE_CORRUPTED';
    public const FILE_CORRUPTED_MSG = 'File is corrupted';
    public const STORAGE_QUOTA_EXCEEDED = 'STORAGE_QUOTA_EXCEEDED';
    public const STORAGE_QUOTA_EXCEEDED_MSG = 'Storage quota exceeded';
    public const FILE_PROCESSING_ERROR = 'FILE_PROCESSING_ERROR';
    public const FILE_PROCESSING_ERROR_MSG = 'File processing error';

    // Email & Notification Errors
    public const EMAIL_SEND_FAILED = 'EMAIL_SEND_FAILED';
    public const EMAIL_SEND_FAILED_MSG = 'Email send failed';
    public const INVALID_EMAIL_TEMPLATE = 'INVALID_EMAIL_TEMPLATE';
    public const INVALID_EMAIL_TEMPLATE_MSG = 'Invalid email template';
    public const NOTIFICATION_SEND_FAILED = 'NOTIFICATION_SEND_FAILED';
    public const NOTIFICATION_SEND_FAILED_MSG = 'Notification send failed';
    public const MAIL_SERVER_ERROR = 'MAIL_SERVER_ERROR';
    public const MAIL_SERVER_ERROR_MSG = 'Mail server error';

    // Data Import/Export Errors
    public const IMPORT_FAILED = 'IMPORT_FAILED';
    public const IMPORT_FAILED_MSG = 'Import failed';
    public const EXPORT_FAILED = 'EXPORT_FAILED';
    public const EXPORT_FAILED_MSG = 'Export failed';
    public const INVALID_IMPORT_FORMAT = 'INVALID_IMPORT_FORMAT';
    public const INVALID_IMPORT_FORMAT_MSG = 'Invalid import format';
    public const IMPORT_DATA_CORRUPTED = 'IMPORT_DATA_CORRUPTED';
    public const IMPORT_DATA_CORRUPTED_MSG = 'Import data corrupted';
    public const EXPORT_PERMISSION_DENIED = 'EXPORT_PERMISSION_DENIED';
    public const EXPORT_PERMISSION_DENIED_MSG = 'Export permission denied';

    // Cache & Session Errors
    public const CACHE_ERROR = 'CACHE_ERROR';
    public const CACHE_ERROR_MSG = 'Cache error';
    public const SESSION_EXPIRED = 'SESSION_EXPIRED';
    public const SESSION_EXPIRED_MSG = 'Session expired';
    public const SESSION_INVALID = 'SESSION_INVALID';
    public const SESSION_INVALID_MSG = 'Session invalid';
    public const CACHE_CONNECTION_FAILED = 'CACHE_CONNECTION_FAILED';
    public const CACHE_CONNECTION_FAILED_MSG = 'Cache connection failed';

    // Business Logic Errors
    public const OPERATION_NOT_ALLOWED = 'OPERATION_NOT_ALLOWED';
    public const OPERATION_NOT_ALLOWED_MSG = 'Operation not allowed';
    public const INVALID_OPERATION = 'INVALID_OPERATION';
    public const INVALID_OPERATION_MSG = 'Invalid operation';
    public const CONFLICT = 'CONFLICT';
    public const CONFLICT_MSG = 'Conflict occurred';
    public const PRECONDITION_FAILED = 'PRECONDITION_FAILED';
    public const PRECONDITION_FAILED_MSG = 'Precondition failed';
    public const RESOURCE_LOCKED = 'RESOURCE_LOCKED';
    public const RESOURCE_LOCKED_MSG = 'Resource is locked';
    public const RESOURCE_EXPIRED = 'RESOURCE_EXPIRED';
    public const RESOURCE_EXPIRED_MSG = 'Resource has expired';
    public const DEPENDENCY_ERROR = 'DEPENDENCY_ERROR';
    public const DEPENDENCY_ERROR_MSG = 'Dependency error';

    // Third-party Integration Errors
    public const PAYMENT_FAILED = 'PAYMENT_FAILED';
    public const PAYMENT_FAILED_MSG = 'Payment failed';
    public const PAYMENT_GATEWAY_ERROR = 'PAYMENT_GATEWAY_ERROR';
    public const PAYMENT_GATEWAY_ERROR_MSG = 'Payment gateway error';
    public const SMS_SEND_FAILED = 'SMS_SEND_FAILED';
    public const SMS_SEND_FAILED_MSG = 'SMS send failed';
    public const SOCIAL_LOGIN_FAILED = 'SOCIAL_LOGIN_FAILED';
    public const SOCIAL_LOGIN_FAILED_MSG = 'Social login failed';
    public const API_INTEGRATION_ERROR = 'API_INTEGRATION_ERROR';
    public const API_INTEGRATION_ERROR_MSG = 'API integration error';

    // Monitoring & Analytics Errors
    public const ANALYTICS_ERROR = 'ANALYTICS_ERROR';
    public const ANALYTICS_ERROR_MSG = 'Analytics error';
    public const TRACKING_FAILED = 'TRACKING_FAILED';
    public const TRACKING_FAILED_MSG = 'Tracking failed';
    public const METRICS_UNAVAILABLE = 'METRICS_UNAVAILABLE';
    public const METRICS_UNAVAILABLE_MSG = 'Metrics unavailable';
    public const REPORT_GENERATION_FAILED = 'REPORT_GENERATION_FAILED';
    public const REPORT_GENERATION_FAILED_MSG = 'Report generation failed';

    // Certificate Errors
    public const CERTIFICATE_UNSUPPORTED_FORMAT = 'Unsupported format';
    public const CERTIFICATE_UNSUPPORTED_TEMPLATE = 'Unsupported template';
    public const CERTIFICATE_NOT_FOUND = 'Certificate not found';
    public const CERTIFICATE_UNAUTHORIZED_ACCESS = 'Unauthorized access to certificate';
}
