# PDF File Upload Fix Documentation

## Problem
When uploading PDF files for collection deposits, files were being stored as `NULL` in the database instead of being saved with the correct file path.

**Database Issue:**
```
| id | deposit_slip |
+----|--------------|
|  1 | NULL         |
|  2 | NULL         |
```

## Root Causes Identified
1. **Silent failures** - File upload errors weren't being logged or reported to the user
2. **Missing error handling** - Invalid files weren't throwing meaningful exceptions
3. **No file validation feedback** - Frontend showed file was uploaded, but backend silently failed
4. **Potential FormData header issue** - Content-Type wasn't explicitly set for multipart uploads

## Fixes Applied

### 1. Enhanced Backend Error Logging (OperatingAccountController.php)
✅ Added comprehensive error logging for file uploads:
- Logs file validity checks
- Logs storage success/failure with file paths
- Includes collection index for debugging
- Throws detailed exceptions on upload failure

**Before:**
```php
if ($file->isValid()) {
    $filePath = $file->store('deposit-slips/' . $id, 'public');
    $collection->deposit_slip = $filePath;
}
// Silent fail - no error handling!
```

**After:**
```php
if (!$file->isValid()) {
    \Log::error("Invalid file for collection {$index}", ['error' => $file->getErrorMessage()]);
    throw new \Exception("Upload failed for collection " . ($index + 1) . ": " . $file->getErrorMessage());
}

try {
    $filePath = $file->store('deposit-slips/' . $id, 'public');
    $collection->deposit_slip = $filePath;
    \Log::info("File stored successfully", ['file_path' => $filePath]);
} catch (\Exception $e) {
    \Log::error("Failed to store file", ['error' => $e->getMessage()]);
    throw new \Exception("Failed to store file: " . $e->getMessage());
}
```

### 2. Improved Frontend Error Handling (AddCollection.vue)
✅ Enhanced Vue component error messages:
- Explicit Content-Type header for multipart/form-data
- Handle validation errors (status 422)
- Handle file size errors (status 413)
- Show detailed error messages in dialog
- Console log full error response for debugging

**Updated Axios call:**
```javascript
window.axios.post(url, formData, {
    headers: {
        'Content-Type': 'multipart/form-data',
    }
})
```

### 3. Backend Model Initialization (Collection.php)
✅ Explicitly initialize file fields as NULL:
```php
$collection = new Collection([
    // ... other fields
    'deposit_slip' => null,
    'check' => null,
]);
```

### 4. Storage Configuration ✅
✅ Verified storage symlink is created:
```
The [public/storage] link has been connected to [storage/app/public]
```

✅ Storage directory structure confirmed:
```
D:\Devops\Dcash\DailyCashDeposit\storage\app\public  [Directory - Accessible]
```

## File Validation Rules
- **Allowed MIME types**: JPEG, JPG, PNG, GIF, PDF
- **Maximum file size**: 5 MB (5120 KB)
- **Storage locations**:
  - Deposit slips: `storage/app/public/deposit-slips/{account_id}/`
  - Checks: `storage/app/public/checks/{account_id}/`
- **Public access**: Files accessible via `/storage/` URL after upload

## Testing Steps

### 1. Test PDF Upload
1. Navigate to: `http://dc.intrastrata.com/treasury3/operating-account`
2. Add a new collection
3. Upload a PDF file as deposit slip
4. Expected result:
   - File uploads successfully
   - File path appears in database (e.g., `deposit-slips/1/filename.pdf`)
   - File accessible via `/storage/deposit-slips/1/filename.pdf`

### 2. Check Error Messages
- Try uploading file > 5MB
  - Expected: "File size must be less than 5MB" error
- Try uploading unsupported format (e.g., .exe)
  - Expected: "Only images (JPG, PNG, GIF) and PDF files are allowed" error
- Check browser console for detailed error logs

### 3. Verify Server Logs
Check logs for upload activity:
```bash
tail -f storage/logs/laravel.log | grep -i "deposit\|check\|file"
```

## Migration Commands

Run existing migration to clean up any `'0'` values:
```bash
php artisan migrate
```

This will run: `2026_04_15_fix_collection_file_paths.php`

## Database Queries for Verification

Check if PDFs are storing correctly:
```sql
-- Check upload status
SELECT id, operating_account_id, collection_amount, deposit_slip, `check` 
FROM collections 
WHERE deposit_slip IS NOT NULL OR `check` IS NOT NULL;

-- Look for NULL values (should be empty after fix)
SELECT id, deposit_slip 
FROM collections 
WHERE deposit_slip = '0' OR deposit_slip = '';
```

## Common Issues & Solutions

### Issue: Files still showing NULL
**Solution:**
1. Check `storage/logs/laravel.log` for error messages
2. Verify storage directory permissions: `php artisan storage:link`
3. Check disk space: `df -h`
4. Ensure database migration ran: `php artisan migrate:status`

### Issue: 413 Payload Too Large
**Solution:**
- Check php.ini: `upload_max_filesize = 20M` (minimum)
- Check php.ini: `post_max_size = 20M` (minimum)
- Increase if needed and restart PHP

### Issue: Files upload but 404 on download
**Solution:**
1. Verify storage symlink: `ls -la public/storage`
2. Check file exists: `ls -la storage/app/public/deposit-slips/1/`
3. Check Laravel route: `php artisan route:list | grep download`

## Files Modified

1. ✅ `app/Http/Controllers/OperatingAccountController.php` - Enhanced addCollection() with error logging
2. ✅ `resources/js/Pages/Treasury3/OperatingAccount/AddCollection.vue` - Improved error handling
3. ✅ `database/migrations/2026_04_15_fix_collection_file_paths.php` - Clean up invalid '0' values (already run)

## Next Steps

1. Run database migration: `php artisan migrate`
2. Clear application cache: `php artisan config:cache`
3. Test PDF upload in browser
4. Monitor logs: `storage/logs/laravel.log`
5. Verify files in storage directory

## API Endpoints

**Upload Collections with Files:**
```
POST /treasury3/operating-account/{id}/collection
Content-Type: multipart/form-data

Body:
  collections[0][collection_amount]: 10000
  collections[0][assured]: Insurance Company ABC
  collections[0][policy_number]: POL-123456
  collections[0][broker_agent]: John Doe
  collections[0][deposit_slip]: [PDF file]
  collections[0][check]: [PDF file - optional]
```

**Download Collection File:**
```
GET /treasury3/collection/{collectionId}/download/{fileType}
  {fileType}: 'deposit-slip' or 'check'

Response: File download
```

## Performance Notes

- Files are stored on disk (not in database)
- Database stores only the file path
- Files accessible via public storage URL
- Download endpoint includes security validation
- Recommended: Implement file cleanup jobs for deleted collections
