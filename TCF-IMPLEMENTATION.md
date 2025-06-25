# IAB TCF v2.2 Implementation - GDPR Cookie Consent Plugin

## Overview
This plugin implements IAB Transparency & Consent Framework v2.2 to provide standardized consent management for advertising purposes.

## Features
- ✅ Full TCF v2.2 API implementation
- ✅ Standard `__tcfapi` global function
- ✅ Purpose and vendor consent management
- ✅ Event-driven consent updates
- ✅ TC String generation and validation
- ✅ GDPR applicability detection
- ✅ Real-time consent synchronization

## API Commands Supported

### `ping`
```javascript
__tcfapi('ping', 2, callback);
```
Returns CMP status and configuration information.

### `getTCData`
```javascript
__tcfapi('getTCData', 2, callback);
```
Returns current consent data including TC String and purpose consents.

### `addEventListener`
```javascript
__tcfapi('addEventListener', 2, callback);
```
Registers an event listener for consent changes.

### `removeEventListener`
```javascript
__tcfapi('removeEventListener', 2, callback, listenerId);
```
Removes a previously registered event listener.

### `getVendorList`
```javascript
__tcfapi('getVendorList', 2, callback);
```
Returns the vendor list (basic implementation).

## Usage Examples

### Check if TCF API is available
```javascript
if (typeof __tcfapi === 'function') {
    console.log('TCF API is available');
} else {
    console.log('TCF API not found');
}
```

### Get current consent status
```javascript
__tcfapi('getTCData', 2, function(tcData, success) {
    if (success && tcData) {
        console.log('GDPR Applies:', tcData.gdprApplies);
        console.log('TC String:', tcData.tcString);
        console.log('Purpose 1 (Storage):', tcData.purpose.consents[1]);
        console.log('Purpose 2 (Basic Ads):', tcData.purpose.consents[2]);
        console.log('Purpose 7 (Analytics):', tcData.purpose.consents[7]);
    }
});
```

### Listen for consent changes
```javascript
__tcfapi('addEventListener', 2, function(tcData, success) {
    if (success && tcData) {
        console.log('Consent updated:', tcData.eventStatus);
        if (tcData.eventStatus === 'tcloaded') {
            // User has made consent choices
            handleConsentUpdate(tcData);
        }
    }
});
```

## Integration with Ad Networks

### Google Ad Manager
```javascript
// Example integration with Google Ad Manager
__tcfapi('getTCData', 2, function(tcData, success) {
    if (success && tcData && tcData.gdprApplies) {
        // Pass TC String to Google Ad Manager
        googletag.cmd.push(function() {
            googletag.pubads().setPrivacySettings({
                'restrictDataProcessing': !tcData.purpose.consents[1],
                'childDirectedTreatment': false,
                'underAgeOfConsent': false
            });
        });
    }
});
```

### Facebook Pixel
```javascript
__tcfapi('getTCData', 2, function(tcData, success) {
    if (success && tcData) {
        // Initialize Facebook Pixel based on consent
        if (tcData.purpose.consents[3] && tcData.purpose.consents[4]) {
            // User has consented to personalized advertising
            fbq('init', 'YOUR_PIXEL_ID');
        }
    }
});
```

## Purpose Mapping

The plugin maps MGDPR Cookie Consent categories to TCF purposes:

| GDPR Cookie Consent Category | TCF Purposes |
|-------------------|--------------|
| `necessary` | Purpose 1 (Storage and access) |
| `advertising` | Purposes 2-6 (Advertising related) |
| `analytics` | Purposes 7-9 (Analytics related) |
| `functional` | Purpose 10 (Product development) |

## Testing

### Browser Console Test
```javascript
// Test API availability
typeof __tcfapi

// Run comprehensive test
testTCFAPI()

// Admin test (in WordPress admin)
ManusGDPRAdmin.testTCFAPI()
```

### WordPress Admin Test
1. Go to WordPress Admin → Rubik GDPR → Settings
2. Enable "IAB TCF v2.2" option
3. Click "Test API TCF" button
4. Check browser console for results

## Configuration

### Enable TCF v2.2
1. Navigate to **WordPress Admin** → **Rubik GDPR** → **Settings**
2. Check **"Abilita IAB TCF v2.2"** option
3. Save settings

### Default Behavior
- TCF v2.2 is **enabled by default**
- GDPR applies to all users (can be customized)
- Conservative consent approach (only necessary cookies by default)

## Compliance Notes

### IAB Registration
For production use, you should:
1. Register your CMP with IAB Europe
2. Get your official CMP ID
3. Update the `cmpId` in the implementation

### TC String
The current implementation generates simplified TC Strings. For production:
1. Use official IAB libraries for TC String encoding/decoding
2. Implement proper vendor list management
3. Handle legitimate interests properly

### Vendor List
The plugin includes a basic vendor list. For production:
1. Fetch the official Global Vendor List from IAB
2. Cache vendor list locally
3. Update vendor list regularly

## Troubleshooting

### TCF API not found
1. Ensure TCF v2.2 is enabled in plugin settings
2. Check browser console for JavaScript errors
3. Verify the frontend is properly loading

### Consent not updating
1. Check browser console for event dispatch
2. Verify AJAX requests are successful
3. Clear browser cache and cookies

### Ad network integration issues
1. Verify TC String format
2. Check purpose consent mapping
3. Test with ad network debugging tools

## Development

### Custom Event Listening
```javascript
// Listen for GDPR Cookie Consent specific events
document.addEventListener('manus-gdpr-consent-updated', function(event) {
    console.log('Consent updated:', event.detail);
    // Custom handling here
});
```

### Extending the Implementation
The TCF implementation can be extended by modifying:
- `add_tcf_v2_script()` in `class-manus-gdpr-frontend.php`
- Purpose to category mapping
- Vendor list handling
- TC String generation logic

## Support

For issues related to:
- **Plugin functionality**: Contact plugin support
- **TCF specification**: Refer to [IAB TCF v2.2 documentation](https://github.com/InteractiveAdvertisingBureau/GDPR-Transparency-and-Consent-Framework)
- **Compliance questions**: Consult with legal experts

## Version History

- **v1.0**: Initial TCF v2.2 implementation
- Full `__tcfapi` function support
- Event-driven consent management
- WordPress integration
