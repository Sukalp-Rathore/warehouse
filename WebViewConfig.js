// WebView configuration for better mobile experience
export const webViewConfig = {
  // Base URL for your Laravel application
  baseUrl: 'http://localhost/health/public', // Update this to your actual server URL
  
  // User agent for better mobile compatibility
  userAgent: 'Mozilla/5.0 (Linux; Android 10; Mobile) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.120 Mobile Safari/537.36',
  
  // WebView settings
  settings: {
    javaScriptEnabled: true,
    domStorageEnabled: true,
    startInLoadingState: true,
    scalesPageToFit: true,
    allowsInlineMediaPlayback: true,
    mediaPlaybackRequiresUserAction: false,
    mixedContentMode: 'compatibility',
    allowsBackForwardNavigationGestures: true,
    allowsLinkPreview: false,
    decelerationRate: 'normal',
    showsHorizontalScrollIndicator: false,
    showsVerticalScrollIndicator: true,
    bounces: false,
    scrollEnabled: true,
    nestedScrollEnabled: true,
  },
  
  // Headers for better Laravel compatibility
  headers: {
    'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
    'Accept-Language': 'en-US,en;q=0.5',
    'Accept-Encoding': 'gzip, deflate',
    'Connection': 'keep-alive',
    'Upgrade-Insecure-Requests': '1',
  },
  
  // Injected JavaScript for mobile optimizations
  injectedJavaScript: `
    // Disable zoom on double tap
    document.addEventListener('touchstart', function(event) {
      if (event.touches.length > 1) {
        event.preventDefault();
      }
    });
    
    // Prevent zoom on input focus
    var meta = document.createElement('meta');
    meta.name = 'viewport';
    meta.content = 'width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no';
    document.getElementsByTagName('head')[0].appendChild(meta);
    
    // Add mobile-friendly styles
    var style = document.createElement('style');
    style.innerHTML = \`
      body {
        -webkit-text-size-adjust: 100%;
        -webkit-touch-callout: none;
        -webkit-user-select: none;
        -khtml-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
      }
      
      input, textarea, select {
        -webkit-user-select: text;
        -khtml-user-select: text;
        -moz-user-select: text;
        -ms-user-select: text;
        user-select: text;
        font-size: 16px; /* Prevents zoom on iOS */
      }
      
      /* Improve touch targets */
      button, a, input[type="button"], input[type="submit"] {
        min-height: 44px;
        min-width: 44px;
      }
      
      /* Hide scrollbars on mobile */
      ::-webkit-scrollbar {
        display: none;
      }
      
      /* Improve form styling */
      input, select, textarea {
        border-radius: 4px;
        border: 1px solid #ccc;
        padding: 8px;
        font-size: 16px;
      }
    \`;
    document.getElementsByTagName('head')[0].appendChild(style);
    
    // Notify React Native that the page is ready
    window.ReactNativeWebView.postMessage(JSON.stringify({type: 'pageReady'}));
  `,
  
  // Handle messages from WebView
  onMessage: (event) => {
    try {
      const data = JSON.parse(event.nativeEvent.data);
      if (data.type === 'pageReady') {
        console.log('WebView page is ready');
      }
    } catch (error) {
      console.log('WebView message:', event.nativeEvent.data);
    }
  },
  
  // Error handling
  onError: (syntheticEvent) => {
    const { nativeEvent } = syntheticEvent;
    console.warn('WebView error:', nativeEvent);
  },
  
  // HTTP error handling
  onHttpError: (syntheticEvent) => {
    const { nativeEvent } = syntheticEvent;
    console.warn('WebView HTTP error:', nativeEvent);
  },
};
