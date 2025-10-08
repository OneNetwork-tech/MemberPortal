<?php
// Language switcher component
$currentLanguage = getCurrentLanguage();
$languages = [
    'sv' => ['name' => 'Svenska', 'flag' => '🇸🇪'],
    'en' => ['name' => 'English', 'flag' => '🇬🇧']
];
?>

<div class="language-switcher">
    <button class="language-toggle" type="button">
        <span class="current-language">
            <?php echo $languages[$currentLanguage]['flag']; ?>
            <?php echo $languages[$currentLanguage]['name']; ?>
        </span>
        <i class="fas fa-chevron-down"></i>
    </button>
    <div class="language-dropdown">
        <?php foreach ($languages as $code => $language): ?>
            <?php if ($code !== $currentLanguage): ?>
                <a href="?lang=<?php echo $code; ?>" class="language-option">
                    <span class="language-flag"><?php echo $language['flag']; ?></span>
                    <span class="language-name"><?php echo $language['name']; ?></span>
                </a>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
</div>

<style>
.language-switcher {
    position: relative;
    display: inline-block;
}

.language-toggle {
    background: transparent;
    border: 1px solid var(--gray-300);
    border-radius: var(--border-radius);
    padding: 0.5rem 1rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    transition: var(--transition);
    color: var(--gray-700);
}

.language-toggle:hover {
    border-color: var(--primary-color);
    color: var(--primary-color);
}

.current-language {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.language-dropdown {
    position: absolute;
    top: 100%;
    right: 0;
    background: var(--white);
    border: 1px solid var(--gray-300);
    border-radius: var(--border-radius);
    box-shadow: var(--shadow-md);
    min-width: 150px;
    opacity: 0;
    visibility: hidden;
    transform: translateY(-10px);
    transition: var(--transition);
    z-index: 1000;
}

.language-switcher:hover .language-dropdown {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.language-option {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    text-decoration: none;
    color: var(--gray-700);
    transition: var(--transition);
    border-bottom: 1px solid var(--gray-100);
}

.language-option:last-child {
    border-bottom: none;
}

.language-option:hover {
    background: var(--gray-50);
    color: var(--primary-color);
}

.language-flag {
    font-size: 1.2rem;
}

.language-name {
    font-size: 0.875rem;
}

/* Mobile styles */
@media (max-width: 768px) {
    .language-toggle {
        padding: 0.5rem;
    }
    
    .current-language span:last-child {
        display: none;
    }
    
    .language-dropdown {
        right: -50%;
    }
}
</style>