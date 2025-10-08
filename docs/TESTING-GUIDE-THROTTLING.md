# Server-Side Throttling Testing Guide

## Quick Testing Checklist

### Prerequisites
- [ ] Plugin installed and activated
- [ ] reCAPTCHA enabled in settings
- [ ] Site Key and Secret Key configured
- [ ] Frontend shortcode page created

---

## Test 1: Basic Throttling (5-Search Limit)

### Steps
1. Open frontend page with `[geekbench_results]` shortcode
2. Make 5 searches in quick succession:
   - Search 1: "iPhone 16"
   - Search 2: "iPad Pro"
   - Search 3: "MacBook Pro"
   - Search 4: "Apple M4"
   - Search 5: "iPhone 15"
3. Make 6th search: "iPhone 14"

### Expected Results
- ✅ Searches 1-5: All succeed without CAPTCHA
- ✅ Search 6: Error message appears
- ✅ Error: "Please complete the reCAPTCHA verification to continue searching."
- ✅ CAPTCHA widget displays automatically
- ✅ Widget scrolls into view

### Pass Criteria
All searches 1-5 succeed, search 6 requires CAPTCHA.

---

## Test 2: reCAPTCHA Verification

### Steps
1. Continue from Test 1 (6th search blocked)
2. Complete the reCAPTCHA challenge
3. Click search button again
4. Observe results

### Expected Results
- ✅ CAPTCHA challenge appears
- ✅ After completing CAPTCHA, search succeeds
- ✅ Results display correctly
- ✅ Counter resets (can make 5 more searches)

### Pass Criteria
Search succeeds after valid CAPTCHA completion.

---

## Test 3: Counter Reset After CAPTCHA

### Steps
1. Continue from Test 2 (just completed CAPTCHA)
2. Make 5 more searches without CAPTCHA:
   - Search 7: "iPhone 13"
   - Search 8: "iPad Air"
   - Search 9: "MacBook Air"
   - Search 10: "Apple M3"
   - Search 11: "iPhone 12"
3. Make 12th search: "iPhone 11"

### Expected Results
- ✅ Searches 7-11: All succeed without CAPTCHA
- ✅ Search 12: CAPTCHA required again
- ✅ Counter properly reset after previous CAPTCHA

### Pass Criteria
Counter resets to 0 after CAPTCHA, allowing 5 more searches.

---

## Test 4: Transient Expiration (5-Minute Window)

### Steps
1. Make 3 searches
2. Wait 6 minutes (set a timer)
3. Make another search

### Expected Results
- ✅ First 3 searches succeed
- ✅ After 6 minutes, 4th search succeeds without CAPTCHA
- ✅ Counter expired and reset to 0

### Pass Criteria
Search after 6 minutes succeeds without CAPTCHA.

---

## Test 5: Bypass Prevention (JavaScript Manipulation)

### Steps
1. Open browser DevTools (F12)
2. Go to Console tab
3. Make 5 searches normally
4. In console, type: `searchCount = 0` (press Enter)
5. Make 6th search

### Expected Results
- ✅ Client-side variable changes to 0
- ✅ Server still requires CAPTCHA (ignores client-side)
- ✅ Error: "Please complete the reCAPTCHA verification"
- ✅ Bypass attempt fails

### Pass Criteria
Server enforces limit regardless of client-side manipulation.

---

## Test 6: Multiple IPs (Different Users)

### Steps
1. User A: Make 5 searches from IP 1
2. User B: Make 5 searches from IP 2 (different device/network)
3. User A: Make 6th search
4. User B: Make 6th search

### Expected Results
- ✅ User A: 6th search requires CAPTCHA
- ✅ User B: 6th search requires CAPTCHA
- ✅ Counters are independent per IP
- ✅ No interference between users

### Pass Criteria
Each IP has its own independent counter.

---

## Test 7: Invalid CAPTCHA

### Steps
1. Make 6 searches to trigger CAPTCHA
2. Complete CAPTCHA
3. Wait 2 minutes (CAPTCHA expires)
4. Click search button

### Expected Results
- ✅ Error: "reCAPTCHA verification failed. Please try again."
- ✅ CAPTCHA widget remains visible
- ✅ Search does not succeed

### Pass Criteria
Expired/invalid CAPTCHA is rejected by server.

---

## Test 8: reCAPTCHA Disabled

### Steps
1. Go to WP Admin → Tools → Geekbench Settings
2. Uncheck "Enable reCAPTCHA"
3. Save settings
4. Make 6 searches on frontend

### Expected Results
- ✅ Search 6: Error message appears
- ✅ Error: "Search limit reached. Please enable reCAPTCHA in settings to continue."
- ✅ No CAPTCHA widget displays
- ✅ Cannot proceed without enabling reCAPTCHA

### Pass Criteria
Throttling still works, but blocks searches when reCAPTCHA disabled.

---

## Test 9: Proxy/CDN IP Detection

### Steps (if behind Cloudflare/proxy)
1. Check server headers: `$_SERVER['HTTP_CF_CONNECTING_IP']`
2. Make 5 searches
3. Check transient key in database

### Expected Results
- ✅ Real IP detected (not proxy IP)
- ✅ Transient key uses real IP
- ✅ Throttling works correctly

### Pass Criteria
Real IP detected behind proxy/CDN.

---

## Test 10: Database Cleanup

### Steps
1. Make 3 searches
2. Check database: `SELECT * FROM wp_options WHERE option_name LIKE '%geekbench_throttle%'`
3. Wait 6 minutes
4. Check database again

### Expected Results
- ✅ After searches: 2 rows exist (value + timeout)
- ✅ After 6 minutes: Rows deleted automatically
- ✅ No manual cleanup needed

### Pass Criteria
Transients auto-delete after expiration.

---

## Advanced Testing

### Test 11: Concurrent Requests
**Goal**: Test race conditions

1. Open 2 browser tabs
2. Make searches simultaneously
3. Verify counter increments correctly

### Test 12: Load Testing
**Goal**: Test performance under load

1. Use tool like Apache Bench or JMeter
2. Send 100 requests from same IP
3. Verify throttling works
4. Check server performance

### Test 13: Error Handling
**Goal**: Test edge cases

1. Invalid reCAPTCHA secret key
2. Google API timeout
3. Database connection failure
4. Verify graceful error handling

---

## Debugging Tools

### Check Transient in Database
```sql
SELECT * FROM wp_options 
WHERE option_name LIKE '%geekbench_throttle%';
```

### Check Transient Value
```php
// In WordPress admin or WP-CLI
$ip = '192.168.1.1'; // Replace with actual IP
$key = 'geekbench_throttle_' . md5($ip);
$value = get_transient($key);
echo "Count: " . $value;
```

### Clear Transient Manually
```php
// In WordPress admin or WP-CLI
$ip = '192.168.1.1'; // Replace with actual IP
$key = 'geekbench_throttle_' . md5($ip);
delete_transient($key);
echo "Transient cleared!";
```

### Check Current IP
```php
// Add to src/Shortcode.php temporarily
error_log('User IP: ' . $this->get_user_ip());
```

### Monitor AJAX Requests
1. Open DevTools → Network tab
2. Filter: XHR
3. Make search
4. Check request/response
5. Look for `requires_captcha` flag

---

## Common Issues & Solutions

### Issue: CAPTCHA Required Immediately
**Cause**: Transient already exists  
**Solution**: Wait 5 minutes or clear transient manually

### Issue: Throttling Not Working
**Cause**: reCAPTCHA not enabled  
**Solution**: Enable in WP Admin → Tools → Geekbench Settings

### Issue: Wrong IP Detected
**Cause**: Proxy header not detected  
**Solution**: Add header to `$ip_keys` array in `get_user_ip()`

### Issue: Transients Not Expiring
**Cause**: WordPress cron not running  
**Solution**: Check WP Cron status, run manually if needed

### Issue: CAPTCHA Always Fails
**Cause**: Invalid secret key  
**Solution**: Verify secret key in settings matches Google reCAPTCHA admin

---

## Performance Benchmarks

### Expected Performance
- **Search 1-5**: < 500ms response time
- **Search 6 (CAPTCHA check)**: < 800ms response time
- **Database queries**: 2 per search (get + set transient)
- **Memory usage**: < 1MB per request

### Monitoring
```bash
# Check WordPress debug log
tail -f wp-content/debug.log

# Check PHP error log
tail -f /var/log/php/error.log

# Monitor database queries
# Enable SAVEQUERIES in wp-config.php
define('SAVEQUERIES', true);
```

---

## Regression Testing

After any code changes, re-run:
- [ ] Test 1: Basic Throttling
- [ ] Test 2: reCAPTCHA Verification
- [ ] Test 5: Bypass Prevention
- [ ] Test 10: Database Cleanup

---

## Sign-Off Checklist

### Functionality
- [ ] All 10 basic tests pass
- [ ] No console errors
- [ ] No PHP errors in logs
- [ ] CAPTCHA integration works

### Performance
- [ ] Response times acceptable
- [ ] No database bloat
- [ ] Transients expire correctly
- [ ] No memory leaks

### Security
- [ ] Bypass prevention works
- [ ] IP detection accurate
- [ ] reCAPTCHA verification works
- [ ] Error messages appropriate

### User Experience
- [ ] Error messages clear
- [ ] CAPTCHA displays correctly
- [ ] Smooth transitions
- [ ] No confusing behavior

---

**Testing Complete**: ✅ All tests passed  
**Tested By**: _________________  
**Date**: _________________  
**Environment**: _________________  
**Notes**: _________________

