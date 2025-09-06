<?php

return [
    'Contracts\UserRepositoryInterface' => 'Eloquent\UserRepository',
    'Contracts\CategoryRepositoryInterface' => 'Eloquent\CategoryRepository',
    'Contracts\TestRepositoryInterface' => 'Eloquent\TestRepository',
    'Contracts\QuestionRepositoryInterface' => 'Eloquent\QuestionRepository',
    'Contracts\TestAttemptRepositoryInterface' => 'Eloquent\TestAttemptRepository',
    'Contracts\CertificateRepositoryInterface' => 'Eloquent\CertificateRepository',
    'Contracts\CertificateTemplateInterface' => 'Services\Templates\DefaultCertificateTemplate',
    'Contracts\CertificateGeneratorInterface' => 'Services\PdfCertificateGenerator',
];
