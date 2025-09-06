<?php

/**
 * Error messages configuration file.
 * This file contains all the error messages used in the application.
 * Each error message is associated with a unique key and includes a message,
 * details, and an HTTP status code.
 */ 
return [
    // Authentication & Authorization
    'UNAUTHENTICATED' => [
        'message' => 'Authentication required',
        'details' => 'Please login to access this resource',
        'status' => 401
    ],
    'TOKEN_EXPIRED' => [
        'message' => 'Token has expired',
        'details' => 'Please refresh your token or login again',
        'status' => 401
    ],
    'TOKEN_INVALID' => [
        'message' => 'Token is invalid',
        'details' => 'Please login again with valid credentials',
        'status' => 401
    ],
    'TOKEN_ABSENT' => [
        'message' => 'Token is required',
        'details' => 'Authorization token is missing from request',
        'status' => 401
    ],
    'FORBIDDEN' => [
        'message' => 'Access forbidden',
        'details' => 'You don\'t have permission to access this resource',
        'status' => 403
    ],
    'ACCESS_DENIED' => [
        'message' => 'Access denied',
        'details' => 'Insufficient privileges to perform this action',
        'status' => 403
    ],
    'INSUFFICIENT_PERMISSIONS' => [
        'message' => 'Insufficient permissions',
        'details' => 'You don\'t have permission to perform this action',
        'status' => 403
    ],
    'INVALID_CREDENTIALS' => [
        'message' => 'Invalid credentials',
        'details' => 'Email or password is incorrect',
        'status' => 401
    ],
    'ACCOUNT_DISABLED' => [
        'message' => 'Account disabled',
        'details' => 'Your account has been deactivated',
        'status' => 403
    ],

    // Validation
    'VALIDATION_ERROR' => [
        'message' => 'Validation failed',
        'details' => 'The given data was invalid',
        'status' => 422
    ],
    'INVALID_INPUT' => [
        'message' => 'Invalid input provided',
        'details' => 'Please check your input and try again',
        'status' => 400
    ],
    'EMAIL_ALREADY_EXISTS' => [
        'message' => 'Email already exists',
        'details' => 'An account with this email address already exists',
        'status' => 409
    ],

    // Resources
    'NOT_FOUND' => [
        'message' => 'Resource not found',
        'details' => 'The requested resource could not be found',
        'status' => 404
    ],
    'USER_NOT_FOUND' => [
        'message' => 'User not found',
        'details' => 'The requested user does not exist',
        'status' => 404
    ],
    'TEST_NOT_FOUND' => [
        'message' => 'Test not found',
        'details' => 'The requested test does not exist or has been deleted',
        'status' => 404
    ],
    'CATEGORY_NOT_FOUND' => [
        'message' => 'Category not found',
        'details' => 'The requested category does not exist',
        'status' => 404
    ],
    'QUESTION_NOT_FOUND' => [
        'message' => 'Question not found',
        'details' => 'The requested question does not exist',
        'status' => 404
    ],

    // User Management
    'USER_ALREADY_TEACHER' => [
        'message' => 'User is already a teacher',
        'details' => 'This user already has teacher privileges',
        'status' => 400
    ],
    'USER_ALREADY_ACTIVE' => [
        'message' => 'User is already active',
        'details' => 'This user account is already active',
        'status' => 400
    ],
    'USER_ALREADY_INACTIVE' => [
        'message' => 'User is already inactive',
        'details' => 'This user account is already inactive',
        'status' => 400
    ],
    'CANNOT_DELETE_YOURSELF' => [
        'message' => 'Cannot delete your own account',
        'details' => 'You cannot delete your own user account',
        'status' => 400
    ],
    'CANNOT_DEACTIVATE_YOURSELF' => [
        'message' => 'Cannot deactivate your own account',
        'details' => 'You cannot deactivate your own user account',
        'status' => 400
    ],
    'USER_HAS_DEPENDENCIES' => [
        'message' => 'User has dependencies',
        'details' => 'Cannot perform this action because user has associated data',
        'status' => 400
    ],

    // Category Management
    'CATEGORY_IN_USE' => [
        'message' => 'Category is in use',
        'details' => 'Cannot delete category that has associated tests',
        'status' => 400
    ],
    'CATEGORY_REQUIRED' => [
        'message' => 'Category is required',
        'details' => 'Please select a valid category for the test',
        'status' => 400
    ],
    'INVALID_CATEGORY' => [
        'message' => 'Invalid category',
        'details' => 'The selected category is not active or does not exist',
        'status' => 400
    ],

    // Test Management
    'TEST_ALREADY_STARTED' => [
        'message' => 'Test already started',
        'details' => 'You have already started this test',
        'status' => 422
    ],
    'TEST_ALREADY_COMPLETED' => [
        'message' => 'Test already completed',
        'details' => 'You have already completed this test',
        'status' => 422
    ],
    'TEST_NOT_ACTIVE' => [
        'message' => 'Test not active',
        'details' => 'This test is currently not available',
        'status' => 400
    ],
    'CANNOT_EDIT_OTHER_USER_TEST' => [
        'message' => 'Cannot edit other user\'s test',
        'details' => 'You can only edit tests that you created',
        'status' => 403
    ],
    'TEST_TIME_EXPIRED' => [
        'message' => 'Test time expired',
        'details' => 'The time limit for this test has been exceeded',
        'status' => 400
    ],
    'TEST_HAS_NO_QUESTIONS' => [
        'message' => 'Test has no questions',
        'details' => 'Cannot start test with no questions',
        'status' => 422
    ],
    'CANNOT_MODIFY_PUBLISHED_TEST' => [
        'message' => 'Cannot modify published test',
        'details' => 'Published tests cannot be modified',
        'status' => 400
    ],

    // System
    'INTERNAL_SERVER_ERROR' => [
        'message' => 'Internal server error',
        'details' => 'Something went wrong on our end',
        'status' => 500
    ],
    'DATABASE_ERROR' => [
        'message' => 'Database error',
        'details' => 'A database error occurred',
        'status' => 500
    ],
    'RATE_LIMIT_EXCEEDED' => [
        'message' => 'Rate limit exceeded',
        'details' => 'Too many requests. Please try again later',
        'status' => 429
    ],

    // Operations
    'OPERATION_NOT_ALLOWED' => [
        'message' => 'Operation not allowed',
        'details' => 'This operation is not permitted',
        'status' => 400
    ],
    'INVALID_OPERATION' => [
        'message' => 'Invalid operation',
        'details' => 'The requested operation is invalid',
        'status' => 400
    ],
];