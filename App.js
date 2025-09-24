import React, { useState, useRef } from 'react';
import {
  StyleSheet,
  View,
  Text,
  TouchableOpacity,
  SafeAreaView,
  StatusBar,
  Alert,
  BackHandler,
  Platform,
} from 'react-native';
import { WebView } from 'react-native-webview';
import * as WebBrowser from 'expo-web-browser';

// Replace this URL with your actual Laravel application URL
const WEBVIEW_URL = 'http://localhost/health/public'; // Update this to your server URL

export default function App() {
  const [canGoBack, setCanGoBack] = useState(false);
  const [canGoForward, setCanGoForward] = useState(false);
  const [currentUrl, setCurrentUrl] = useState(WEBVIEW_URL);
  const [isLoading, setIsLoading] = useState(true);
  const webViewRef = useRef(null);

  // Handle Android back button
  React.useEffect(() => {
    const backAction = () => {
      if (canGoBack) {
        webViewRef.current?.goBack();
        return true;
      }
      return false;
    };

    const backHandler = BackHandler.addEventListener('hardwareBackPress', backAction);
    return () => backHandler.remove();
  }, [canGoBack]);

  const handleNavigationStateChange = (navState) => {
    setCanGoBack(navState.canGoBack);
    setCanGoForward(navState.canGoForward);
    setCurrentUrl(navState.url);
  };

  const handleLoadStart = () => {
    setIsLoading(true);
  };

  const handleLoadEnd = () => {
    setIsLoading(false);
  };

  const handleError = (syntheticEvent) => {
    const { nativeEvent } = syntheticEvent;
    console.warn('WebView error: ', nativeEvent);
    Alert.alert(
      'Connection Error',
      'Unable to load the application. Please check your internet connection and try again.',
      [{ text: 'OK' }]
    );
  };

  const goBack = () => {
    if (canGoBack) {
      webViewRef.current?.goBack();
    }
  };

  const goForward = () => {
    if (canGoForward) {
      webViewRef.current?.goForward();
    }
  };

  const refresh = () => {
    webViewRef.current?.reload();
  };

  const openInBrowser = () => {
    WebBrowser.openBrowserAsync(currentUrl);
  };

  return (
    <SafeAreaView style={styles.container}>
      <StatusBar barStyle="dark-content" backgroundColor="#fff" />
      
      {/* Header with navigation controls */}
      <View style={styles.header}>
        <View style={styles.navigationButtons}>
          <TouchableOpacity
            style={[styles.navButton, !canGoBack && styles.navButtonDisabled]}
            onPress={goBack}
            disabled={!canGoBack}
          >
            <Text style={[styles.navButtonText, !canGoBack && styles.navButtonTextDisabled]}>
              ←
            </Text>
          </TouchableOpacity>
          
          <TouchableOpacity
            style={[styles.navButton, !canGoForward && styles.navButtonDisabled]}
            onPress={goForward}
            disabled={!canGoForward}
          >
            <Text style={[styles.navButtonText, !canGoForward && styles.navButtonTextDisabled]}>
              →
            </Text>
          </TouchableOpacity>
          
          <TouchableOpacity style={styles.navButton} onPress={refresh}>
            <Text style={styles.navButtonText}>⟳</Text>
          </TouchableOpacity>
          
          <TouchableOpacity style={styles.navButton} onPress={openInBrowser}>
            <Text style={styles.navButtonText}>🌐</Text>
          </TouchableOpacity>
        </View>
        
        {isLoading && (
          <View style={styles.loadingContainer}>
            <Text style={styles.loadingText}>Loading...</Text>
          </View>
        )}
      </View>

      {/* WebView */}
      <WebView
        ref={webViewRef}
        source={{ uri: WEBVIEW_URL }}
        style={styles.webview}
        onNavigationStateChange={handleNavigationStateChange}
        onLoadStart={handleLoadStart}
        onLoadEnd={handleLoadEnd}
        onError={handleError}
        javaScriptEnabled={true}
        domStorageEnabled={true}
        startInLoadingState={true}
        scalesPageToFit={true}
        allowsInlineMediaPlayback={true}
        mediaPlaybackRequiresUserAction={false}
        mixedContentMode="compatibility"
        userAgent="Mozilla/5.0 (Linux; Android 10; Mobile) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.120 Mobile Safari/537.36"
        onShouldStartLoadWithRequest={(request) => {
          // Allow navigation within the same domain
          return request.url.startsWith(WEBVIEW_URL) || request.url.includes('localhost');
        }}
      />
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#fff',
  },
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: 10,
    paddingVertical: 8,
    borderBottomWidth: 1,
    borderBottomColor: '#e0e0e0',
    backgroundColor: '#f8f9fa',
  },
  navigationButtons: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  navButton: {
    paddingHorizontal: 12,
    paddingVertical: 8,
    marginHorizontal: 2,
    backgroundColor: '#007bff',
    borderRadius: 6,
    minWidth: 40,
    alignItems: 'center',
  },
  navButtonDisabled: {
    backgroundColor: '#6c757d',
  },
  navButtonText: {
    color: '#fff',
    fontSize: 16,
    fontWeight: 'bold',
  },
  navButtonTextDisabled: {
    color: '#adb5bd',
  },
  loadingContainer: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
  },
  loadingText: {
    color: '#666',
    fontSize: 14,
  },
  webview: {
    flex: 1,
  },
});
