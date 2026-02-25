        </main><!-- /main max-w-7xl mx-auto w-full (opened in sidebar-admin.php) -->

        <!-- Admin Footer -->
        <footer class="mt-auto px-6 py-10 border-t border-vercel-gray-200 bg-white">
            <div class="max-w-7xl mx-auto w-full flex flex-col md:flex-row items-center justify-between gap-6">
                <div class="flex flex-col sm:flex-row items-center gap-4 text-vercel-gray-400 text-[13px]">
                    <p>&copy; <?php echo date('Y'); ?> <span class="text-vercel-black font-semibold tracking-tight">Babybib Admin</span>. <?php echo __('admin_footer_rights'); ?></p>
                </div>
                
                <div class="flex items-center gap-8">
                    <a href="<?php echo SITE_URL; ?>" target="_blank" class="flex items-center gap-2 text-vercel-gray-400 hover:text-vercel-black transition-colors text-[13px] font-medium">
                        <i data-lucide="external-link" class="w-4 h-4"></i>
                        <span><?php echo __('admin_footer_visit'); ?></span>
                    </a>
                </div>
            </div>
        </footer>

    </div><!-- /flex-flex-col (opened in sidebar-admin.php) -->
</div><!-- /admin-layout (opened in sidebar-admin.php) -->

<!-- Scripts -->
<script>
    // Initialize Lucide icons on dynamic content if needed
    document.addEventListener('DOMContentLoaded', () => {
        if (window.lucide) lucide.createIcons();
    });

    async function adminLogout() {
        if (!confirm('<?php echo __('admin_confirm_logout'); ?>')) return;
        
        try {
            const res = await API.post('<?php echo SITE_URL; ?>/api/auth/logout.php');
            if (res.success) {
                Toast.success('<?php echo __('admin_logged_out'); ?>');
                setTimeout(() => window.location.href = '<?php echo SITE_URL; ?>', 1000);
            }
        } catch (e) {
            window.location.href = '<?php echo SITE_URL; ?>';
        }
    }
</script>

<?php if (isset($extraScripts)) echo $extraScripts; ?>
</body>
</html>