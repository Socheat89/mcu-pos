<?php require_once __DIR__ . '/../../../core/helpers/url.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo __('product_management'); ?> - <?php echo htmlspecialchars($tenantName ?? 'POS'); ?></title>
     <link href="<?php echo mc_base_path(); ?>/public/css/pos_template.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Outfit:wght@300;400;500;600;700;800;900&family=Battambang:wght@100;300;400;700;900&display=swap" rel="stylesheet">
    <style>
        body, h1, h2, h3, h4, h5, h6, p, span, a, button, input, select, textarea {
            font-family: 'Battambang', 'Outfit', 'Inter', sans-serif !important;
        }
        .form-card { background: var(--pos-card); backdrop-filter: blur(12px); border-radius: 24px; padding: 40px; border: 1.5px solid var(--pos-border); max-width: 900px; margin: 0 auto; }
        .upload-zone { border: 2.5px dashed var(--pos-border); border-radius: 20px; padding: 48px; text-align: center; background: rgba(8, 10, 16, 0.4); transition: all 0.3s; cursor: pointer; position: relative; }
        .upload-zone:hover { border-color: var(--pos-primary); background: rgba(6, 182, 212, 0.04); }
        .upload-zone.dragover { border-color: var(--pos-primary); background: rgba(6, 182, 212, 0.1); }
        .preview-img { max-width: 100%; max-height: 280px; border-radius: 16px; margin-top: 20px; box-shadow: var(--pos-shadow-lg); border: 4px solid var(--pos-border); }
    </style>
</head>
<body class="pos-app">
    <?php $activeNav = 'products'; include __DIR__ . '/partials/navbar.php'; ?>

    <div class="fade-in">
        <div style="text-align: center; margin-bottom: 40px;">
            <div style="display: inline-flex; align-items: center; gap: 8px; margin-bottom: 12px; background: rgba(6, 182, 212, 0.1); padding: 8px 16px; border-radius: 12px; color: var(--pos-primary); border: 1px solid rgba(6, 182, 212, 0.2); font-weight: 800; font-size: 12px; text-transform: uppercase; letter-spacing: 1px;">
                <i class="fas fa-box-open"></i> <?php echo __('inventory_control'); ?>
            </div>
            <h1 style="font-size: 36px; font-weight: 900; color: var(--pos-text); margin: 0;"><?php echo isset($product) ? __('record_refinement') : __('new_product_entry'); ?></h1>
            <p style="color: var(--pos-text-muted); margin-top: 8px; font-size: 16px;"><?php echo __('product_configure_msg'); ?></p>
        </div>

        <div class="form-card pos-shadow-xl">
            <form method="POST" enctype="multipart/form-data">
                
                <section style="margin-bottom: 40px;">
                    <h3 style="font-size: 14px; font-weight: 900; color: var(--pos-primary); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 24px; display: flex; align-items: center; gap: 10px;">
                        <span style="width: 24px; height: 1.5px; background: var(--pos-primary);"></span>
                        <?php echo __('primary_details'); ?>
                    </h3>
                    <div class="pos-form-group">
                        <label class="pos-form-label"><?php echo __('full_product_name'); ?> <span style="color:red;">*</span></label>
                        <input type="text" name="name" class="pos-form-control" value="<?php echo htmlspecialchars($product['name'] ?? ''); ?>" required placeholder="<?php echo __('enter_name_placeholder', ['default' => 'Enter descriptive name...']); ?>">
                    </div>
                    <div class="pos-grid cols-2" style="margin-top: 24px;">
                        <div class="pos-form-group">
                            <label class="pos-form-label"><?php echo __('classification_category'); ?></label>
                            <select name="category_id" class="pos-form-control pos-form-select">
                                <option value=""><?php echo __('uncategorized'); ?></option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>" <?php echo (isset($product) && $product['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="pos-form-group">
                            <label class="pos-form-label"><?php echo __('status'); ?></label>
                            <select name="status" class="pos-form-control pos-form-select">
                                <option value="active" <?php echo (!isset($product) || $product['status'] == 'active') ? 'selected' : ''; ?>><?php echo __('active_visible'); ?></option>
                                <option value="inactive" <?php echo (isset($product) && $product['status'] == 'inactive') ? 'selected' : ''; ?>><?php echo __('hidden_archived'); ?></option>
                            </select>
                        </div>
                    </div>
                </section>

                <section style="margin-bottom: 40px;">
                    <h3 style="font-size: 14px; font-weight: 900; color: var(--pos-primary); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 24px; display: flex; align-items: center; gap: 10px;">
                        <span style="width: 24px; height: 1.5px; background: var(--pos-primary);"></span>
                        <?php echo __('inventory_pricing'); ?>
                    </h3>
                    <div class="pos-grid cols-2">
                        <div class="pos-form-group">
                            <label class="pos-form-label"><?php echo __('retail_price'); ?> <span style="color:red;">*</span></label>
                            <input type="number" name="price" step="0.01" class="pos-form-control" value="<?php echo $product['price'] ?? ''; ?>" required placeholder="0.00">
                        </div>
                        <div class="pos-form-group">
                            <label class="pos-form-label"><?php echo __('opening_stock'); ?> <span style="color:red;">*</span></label>
                            <input type="number" name="stock_quantity" class="pos-form-control" value="<?php echo $product['stock_quantity'] ?? 0; ?>" required placeholder="0">
                        </div>
                    </div>
                    <div class="pos-grid cols-2" style="margin-top: 24px;">
                        <div class="pos-form-group">
                            <label class="pos-form-label"><?php echo __('sku_ref_id'); ?></label>
                            <input type="text" name="sku" class="pos-form-control" value="<?php echo htmlspecialchars($product['sku'] ?? ''); ?>" placeholder="E.g., PROD-2024-001">
                        </div>
                        <div class="pos-form-group">
                            <label class="pos-form-label"><?php echo __('barcode_num'); ?></label>
                            <input type="text" name="barcode" class="pos-form-control" value="<?php echo htmlspecialchars($product['barcode'] ?? ''); ?>" placeholder="<?php echo __('scan_barcode_placeholder', ['default' => 'Scan product barcode']); ?>">
                        </div>
                    </div>
                </section>

                <section>
                    <h3 style="font-size: 14px; font-weight: 900; color: var(--pos-primary); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 24px; display: flex; align-items: center; gap: 10px;">
                        <span style="width: 24px; height: 1.5px; background: var(--pos-primary);"></span>
                        <?php echo __('media_metadata'); ?>
                    </h3>
                    <div class="pos-form-group">
                        <label class="pos-form-label"><?php echo __('featured_image'); ?></label>
                        <div class="upload-zone" onclick="document.getElementById('image-input').click()">
                            <input type="file" id="image-input" name="image" accept="image/*" style="display: none;" onchange="previewImage(this)">
                            <div id="upload-placeholder" style="<?php echo (isset($product) && $product['image']) ? 'display:none;' : ''; ?>">
                                <div style="width: 64px; height: 64px; background: rgba(255,255,255,0.03); border: 1px solid var(--pos-border); border-radius: 50%; display: grid; place-items: center; margin: 0 auto 16px; box-shadow: var(--pos-shadow-sm);">
                                    <i class="fas fa-file-export" style="font-size: 24px; color: var(--pos-primary);"></i>
                                </div>
                                <div style="font-weight: 800; color: var(--pos-text); font-size: 15px;"><?php echo __('click_select_drag_msg'); ?></div>
                                <div style="font-size: 13px; color: var(--pos-text-muted); margin-top: 6px; font-weight: 500;"><?php echo __('optimal_size_msg'); ?></div>
                            </div>
                            <div id="image-preview-container" style="<?php echo (isset($product) && $product['image']) ? '' : 'display:none;'; ?>">
                                <?php if (isset($product) && $product['image']): ?>
                                    <img src="<?php echo htmlspecialchars(mc_url('uploads/products/' . $product['image'])); ?>" class="preview-img">
                                    <p style="margin-top: 12px; font-size: 12px; color: var(--pos-text-muted); font-weight: 700;"><?php echo __('click_different_image'); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="pos-form-group" style="margin-top: 32px;">
                        <label class="pos-form-label"><?php echo __('technical_description'); ?></label>
                        <textarea name="description" class="pos-form-control" rows="5" style="resize: none;" placeholder="<?php echo __('technical_desc_placeholder'); ?>"><?php echo htmlspecialchars($product['description'] ?? ''); ?></textarea>
                    </div>
                </section>

                <div style="display: flex; justify-content: flex-end; gap: 16px; margin-top: 48px; border-top: 1.5px solid var(--pos-border); padding-top: 32px;">
                    <a href="<?php echo htmlspecialchars($posUrl('products')); ?>" class="btn btn-outline" style="min-width: 140px;">
                        <?php echo __('cancel'); ?>
                    </a>
                    <button type="submit" class="btn btn-primary" style="min-width: 200px; box-shadow: 0 10px 25px rgba(99, 102, 241, 0.3);">
                        <i class="fas fa-save" style="margin-right: 8px;"></i> <?php echo isset($product) ? __('update_records') : __('save_product'); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function previewImage(input) {
            const file = input.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const placeholder = document.getElementById('upload-placeholder');
                    const previewCont = document.getElementById('image-preview-container');
                    
                    placeholder.style.display = 'none';
                    previewCont.style.display = 'block';
                    previewCont.innerHTML = `<img src="${e.target.result}" class="preview-img"><p style="margin-top: 12px; font-size: 12px; color: var(--pos-text-muted); font-weight: 700;"><?php echo __('click_different_image'); ?></p>`;
                };
                reader.readAsDataURL(file);
            }
        }

        const zone = document.querySelector('.upload-zone');
        ['dragover', 'drop'].forEach(evt => zone.addEventListener(evt, e => e.preventDefault()));
        
        zone.addEventListener('dragover', () => zone.classList.add('dragover'));
        ['dragleave', 'drop'].forEach(evt => zone.addEventListener(evt, () => zone.classList.remove('dragover')));
        
        zone.addEventListener('drop', (e) => {
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                document.getElementById('image-input').files = files;
                previewImage({ files: files });
            }
        });
    </script>
    
    <?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>
