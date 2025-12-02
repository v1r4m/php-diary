<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($user->name); ?>의 일기 - Diary</title>
    <link rel="stylesheet" href="<?php echo e(asset('css/app.css')); ?>">
    <style>
        .public-header {
            text-align: center;
            padding: 3rem 1rem;
            border-bottom: 1px solid #e0e0e0;
            margin-bottom: 2rem;
        }
        .public-header h1 {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        .public-header .username {
            color: #666;
            font-size: 1rem;
        }
        .diary-list {
            max-width: 800px;
            margin: 0 auto;
            padding: 0 1rem;
        }
        .diary-item {
            background: #fff;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            transition: box-shadow 0.2s;
        }
        .diary-item:hover {
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }
        .diary-item a {
            text-decoration: none;
            color: inherit;
        }
        .diary-item h2 {
            font-size: 1.25rem;
            margin-bottom: 0.5rem;
            color: #333;
        }
        .diary-item .date {
            font-size: 0.875rem;
            color: #888;
            margin-bottom: 0.75rem;
        }
        .diary-item .preview {
            color: #555;
            line-height: 1.6;
        }
        .empty-state {
            text-align: center;
            padding: 4rem 1rem;
            color: #666;
        }
        .pagination-wrapper {
            display: flex;
            justify-content: center;
            margin-top: 2rem;
            padding-bottom: 2rem;
        }
        .back-link {
            display: block;
            text-align: center;
            margin-top: 2rem;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <header class="public-header">
            <h1><?php echo e($user->name); ?>의 일기</h1>
            <p class="username">{{ $user->username }}</p>
        </header>

        <div class="diary-list">
            <?php $__empty_1 = true; $__currentLoopData = $diaries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $diary): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <article class="diary-item">
                    <a href="<?php echo e(route('public.diary', ['username' => $user->username, 'diary' => $diary->id])); ?>">
                        <h2><?php echo e($diary->title); ?></h2>
                        <p class="date"><?php echo e($diary->created_at->format('Y년 m월 d일')); ?></p>
                        <p class="preview"><?php echo e(Str::limit($diary->body_ciphertext, 200)); ?></p>
                    </a>
                </article>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="empty-state">
                    <h3>아직 공개된 일기가 없습니다</h3>
                    <p><?php echo e($user->name); ?>님이 공개한 일기가 여기에 표시됩니다.</p>
                </div>
            <?php endif; ?>

            <?php if($diaries->hasPages()): ?>
                <div class="pagination-wrapper">
                    <?php echo e($diaries->links()); ?>

                </div>
            <?php endif; ?>
        </div>

        <a href="/" class="back-link">← 홈으로</a>
    </div>
</body>
</html>
<?php /**PATH /var/www/resources/views/public/profile.blade.php ENDPATH**/ ?>