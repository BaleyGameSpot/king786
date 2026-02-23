package com.view;

import android.content.Context;
import android.graphics.Bitmap;
import android.net.Uri;
import android.net.http.SslError;
import android.os.Build;
import android.os.Handler;
import android.os.Message;
import android.util.AttributeSet;
import android.view.KeyEvent;
import android.view.View;
import android.webkit.ClientCertRequest;
import android.webkit.ConsoleMessage;
import android.webkit.GeolocationPermissions;
import android.webkit.HttpAuthHandler;
import android.webkit.JsPromptResult;
import android.webkit.JsResult;
import android.webkit.PermissionRequest;
import android.webkit.RenderProcessGoneDetail;
import android.webkit.SafeBrowsingResponse;
import android.webkit.SslErrorHandler;
import android.webkit.ValueCallback;
import android.webkit.WebChromeClient;
import android.webkit.WebResourceError;
import android.webkit.WebResourceRequest;
import android.webkit.WebResourceResponse;
import android.webkit.WebSettings;
import android.webkit.WebStorage;
import android.webkit.WebView;
import android.webkit.WebViewClient;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;

import com.fontanalyzer.SystemFont;
import com.general.files.GeneralFunctions;
import com.general.files.MyApp;
import com.buddyverse.providers.R;
import com.utils.WeViewFontConfig;

public class WKWebView extends WebView {
    Context mContext;
    GeneralFunctions generalFunc;

    private static final String defaultColorCode = "#333333";
    private static final String defaultFontSize = "14";

    private WebClient clientListener;

    private String TAG = "";

    private WebViewClient webViewClient;
    private WebChromeClient webChromeClient;

    public enum ContentType {
        DEFAULT(defaultColorCode, defaultFontSize),
        ALERT_DIALOG("#161616", "14");
        String color = "";
        String size = "";

        ContentType(String colorCode, String fontSize) {
            this.color = colorCode;
            this.size = fontSize;
        }
    }

    public WKWebView(@NonNull Context context) {
        super(context);
        this.mContext = context;
        setDefaultConfiguration();
    }

    public WKWebView(@NonNull Context context, @Nullable AttributeSet attrs) {
        super(context, attrs);
        this.mContext = context;
        setDefaultConfiguration();
    }

    public WKWebView(@NonNull Context context, @Nullable AttributeSet attrs, int defStyleAttr) {
        super(context, attrs, defStyleAttr);
        this.mContext = context;
        setDefaultConfiguration();
    }

    public WKWebView(@NonNull Context context, @Nullable AttributeSet attrs, int defStyleAttr, int defStyleRes) {
        super(context, attrs, defStyleAttr, defStyleRes);
        this.mContext = context;
        setDefaultConfiguration();
    }

    private void setDefaultConfiguration() {
        if (generalFunc == null) {
            generalFunc = MyApp.getInstance().getAppLevelGeneralFunc();
        }
        WeViewFontConfig.ASSETS_FONT_NAME = SystemFont.FontStyle.REGULAR.resValue;
        WeViewFontConfig.FONT_FAMILY_NAME = SystemFont.FontStyle.REGULAR.name;
        WeViewFontConfig.FONT_COLOR = defaultColorCode;
        WeViewFontConfig.FONT_SIZE = defaultFontSize;

        this.getSettings().setJavaScriptEnabled(MyApp.isJSEnabled);
        this.getSettings().setCacheMode(WebSettings.LOAD_NO_CACHE);
        this.getSettings().setRenderPriority(WebSettings.RenderPriority.HIGH);

//        this.setLayerType(View.LAYER_TYPE_SOFTWARE, null);
        this.setLayerType(View.LAYER_TYPE_HARDWARE, null);


        this.setVerticalScrollBarEnabled(true);

        new Handler().postDelayed(() -> WKWebView.this.setVerticalScrollBarEnabled(false), 4000);

        this.setHorizontalScrollBarEnabled(false);

        this.setHapticFeedbackEnabled(false);

        this.setOnLongClickListener(v -> true);
        this.setLongClickable(false);

    }

    public void setTag(String TAG) {
        this.TAG = TAG;
    }

    public String getTag() {
        return TAG;
    }

    public void setWebClient(WebClient clientListener) {
        if (clientListener == null) {
            this.setWebViewClient(null);
            this.setWebChromeClient(null);
            return;
        }
        this.clientListener = clientListener;

        webViewClient = new myWebClient(this, this.mContext, this.clientListener);
        webChromeClient = new myWebChromeClient(this, this.mContext, this.clientListener);

        this.setWebViewClient(webViewClient);
        this.setWebChromeClient(webChromeClient);
    }

    public WebViewClient getWebViewClient() {
        if (webViewClient == null) {
            setWebClient(new WebClient() {
            });
        }
        return webViewClient;
    }

    public WebChromeClient getWebChromeClient() {
        if (webChromeClient == null) {
            setWebClient(new WebClient() {
            });
        }
        return webChromeClient;
    }

    @Override
    public void loadUrl(@NonNull String url) {
        super.loadUrl(url);
    }

    public void loadData(String data) {
        super.loadDataWithBaseURL(null, wrapHtmlData(data, ContentType.DEFAULT), "text/html", "UTF-8", null);
    }

    public void loadData(String data, ContentType contentType) {
        super.loadDataWithBaseURL(null, wrapHtmlData(data, contentType), "text/html", "UTF-8", null);
    }

    private String wrapHtmlData(String data, ContentType contentType) {

        try {
            return mContext.getString(generalFunc.isRTLmode() ? R.string.html_custom_rtl : R.string.html_custom, WeViewFontConfig.FONT_FAMILY_NAME, WeViewFontConfig.ASSETS_FONT_NAME, contentType.size, contentType.color, data);
        } catch (Exception ignored) {

        }

        return mContext.getString(generalFunc.isRTLmode() ? R.string.html_rtl : R.string.html, data);
    }

    public interface WebClient {
        @Deprecated
        default boolean shouldOverrideUrlLoading(WKWebView view, String url) {
            return false;
        }

        default boolean shouldOverrideUrlLoading(WKWebView view, WebResourceRequest request) {
            return shouldOverrideUrlLoading(view, request.getUrl().toString());
        }

        default void onPageStarted(WKWebView view, String url, Bitmap favicon) {

        }

        default void onPageFinished(WKWebView view, String url) {

        }

        default void onLoadResource(WKWebView view, String url) {

        }

        default void onPageCommitVisible(WKWebView view, String url) {

        }

        @Deprecated
        @Nullable
        default WebResourceResponse shouldInterceptRequest(WKWebView view, String url) {
            return null;
        }

        @Nullable
        default WebResourceResponse shouldInterceptRequest(WKWebView view, WebResourceRequest request) {
            return shouldInterceptRequest(view, request.getUrl().toString());
        }

        @Deprecated
        default void onTooManyRedirects(WKWebView view, Message cancelMsg, Message continueMsg) {
            cancelMsg.sendToTarget();
        }

        @Deprecated
        default void onReceivedError(WKWebView view, int errorCode, String description, String failingUrl) {

        }

        default void onReceivedError(WKWebView view, WebResourceRequest request, WebResourceError error) {
            if (request.isForMainFrame()) {
                if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.M) {
                    onReceivedError(view,
                            error.getErrorCode(), error.getDescription().toString(),
                            request.getUrl().toString());
                }
            }
        }

        default void onReceivedHttpError(WKWebView view, WebResourceRequest request, WebResourceResponse errorResponse) {

        }

        default void onFormResubmission(WKWebView view, Message dontResend, Message resend) {
            dontResend.sendToTarget();
        }

        default void doUpdateVisitedHistory(WKWebView view, String url, boolean isReload) {

        }

        default void onReceivedSslError(WKWebView view, SslErrorHandler handler, SslError error) {
            handler.cancel();
        }

        default void onReceivedClientCertRequest(WKWebView view, ClientCertRequest request) {
            request.cancel();
        }

        default void onReceivedHttpAuthRequest(WKWebView view, HttpAuthHandler handler, String host, String realm) {
            handler.cancel();
        }

        default boolean shouldOverrideKeyEvent(WKWebView view, KeyEvent event) {
            return false;
        }

        default void onScaleChanged(WKWebView view, float oldScale, float newScale) {

        }

        default void onReceivedLoginRequest(WKWebView view, String realm, @Nullable String account, String args) {

        }

        default boolean onRenderProcessGone(WKWebView view, RenderProcessGoneDetail detail) {
            return false;
        }

        default void onSafeBrowsingHit(WKWebView view, WebResourceRequest request, int threatType, SafeBrowsingResponse callback) {
            if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O_MR1) {
                callback.showInterstitial(true);
            }
        }

        /**
         * WebChromeClient Methods
         */


        default void onProgressChanged(WKWebView view, int newProgress) {

        }

        default void onReceivedTitle(WKWebView view, String title) {

        }

        default void onReceivedIcon(WKWebView view, Bitmap icon) {

        }

        default void onReceivedTouchIconUrl(WKWebView view, String url, boolean precomposed) {

        }

        default void onShowCustomView(View view, WebChromeClient.CustomViewCallback callback) {

        }

        @Deprecated
        default void onShowCustomView(View view, int requestedOrientation, WebChromeClient.CustomViewCallback callback) {

        }

        default void onHideCustomView() {

        }

        default boolean onCreateWindow(WKWebView view, boolean isDialog, boolean isUserGesture, Message resultMsg) {
            return false;
        }

        default void onRequestFocus(WKWebView view) {

        }

        default void onCloseWindow(WKWebView window) {

        }

        default boolean onJsAlert(WKWebView view, String url, String message, JsResult result) {
            return false;
        }

        default boolean onJsConfirm(WKWebView view, String url, String message, JsResult result) {
            return false;
        }

        default boolean onJsPrompt(WKWebView view, String url, String message, String defaultValue, JsPromptResult result) {
            return false;
        }

        default boolean onJsBeforeUnload(WKWebView view, String url, String message, JsResult result) {
            return false;
        }

        @Deprecated
        default void onExceededDatabaseQuota(String url, String databaseIdentifier, long quota, long estimatedDatabaseSize, long totalQuota, WebStorage.QuotaUpdater quotaUpdater) {
            quotaUpdater.updateQuota(quota);
        }

        @Deprecated
        default void onReachedMaxAppCacheSize(long requiredStorage, long quota,
                                              WebStorage.QuotaUpdater quotaUpdater) {
            quotaUpdater.updateQuota(quota);
        }

        default void onGeolocationPermissionsShowPrompt(String origin, GeolocationPermissions.Callback callback) {

        }

        default void onGeolocationPermissionsHidePrompt() {

        }

        default void onPermissionRequest(PermissionRequest request) {
            request.deny();
        }

        default void onPermissionRequestCanceled(PermissionRequest request) {

        }

        @Deprecated
        default boolean onJsTimeout() {
            return true;
        }

        @Deprecated
        default void onConsoleMessage(String message, int lineNumber, String sourceID) {

        }

        default boolean onConsoleMessage(ConsoleMessage consoleMessage) {
            return false;
        }

        @Nullable
        default Bitmap getDefaultVideoPoster() {
            return null;
        }

        @Nullable
        default View getVideoLoadingProgressView() {
            return null;
        }

        default void getVisitedHistory(ValueCallback<String[]> callback) {

        }

        default boolean onShowFileChooser(WKWebView webView, ValueCallback<Uri[]> filePathCallback, WebChromeClient.FileChooserParams fileChooserParams) {
            return false;
        }

        @Deprecated
        default void openFileChooser(ValueCallback<Uri> uploadFile, String acceptType, String capture) {
            uploadFile.onReceiveValue(null);
        }
    }


    private class myWebClient extends WebViewClient {
        WKWebView webView;
        Context mContext;
        WebClient listener;

        public myWebClient(WKWebView webView, Context mContext, WebClient listener) {
            this.webView = webView;
            this.mContext = mContext;
            this.listener = listener;
        }

        @Override
        public boolean shouldOverrideUrlLoading(WebView view, String url) {
            if (listener != null) {
                return listener.shouldOverrideUrlLoading(webView, url);
            }
            return super.shouldOverrideUrlLoading(view, url);
        }

        @Override
        public boolean shouldOverrideUrlLoading(WebView view, WebResourceRequest request) {
            if (listener != null) {
                return listener.shouldOverrideUrlLoading(webView, request);
            }
            return super.shouldOverrideUrlLoading(view, request);
        }

        @Override
        public void onPageStarted(WebView view, String url, Bitmap favicon) {
            if (listener != null) {
                listener.onPageStarted(webView, url, favicon);
                return;
            }
            super.onPageStarted(view, url, favicon);
        }

        @Override
        public void onReceivedError(WebView view, WebResourceRequest request, WebResourceError error) {
            if (listener != null) {
                listener.onReceivedError(webView, request, error);
                return;
            }
            super.onReceivedError(view, request, error);
        }

        @Override
        public void onReceivedError(WebView view, int errorCode, String description, String failingUrl) {
            if (listener != null) {
                listener.onReceivedError(webView, errorCode, description, failingUrl);
                return;
            }
            super.onReceivedError(view, errorCode, description, failingUrl);
        }

        @Override
        public void onPageFinished(WebView view, String url) {
            if (listener != null) {
                listener.onPageFinished(webView, url);
                return;
            }
            super.onPageFinished(view, url);
        }

        @Nullable
        @Override
        public WebResourceResponse shouldInterceptRequest(WebView view, WebResourceRequest request) {
            if (listener != null) {
                return listener.shouldInterceptRequest(webView, request);
            }
            return super.shouldInterceptRequest(view, request);
        }

        @Override
        public void onReceivedHttpAuthRequest(WebView view, HttpAuthHandler handler, String host, String realm) {
            if (listener != null) {
                listener.onReceivedHttpAuthRequest(webView, handler, host, realm);
                return;
            }
            super.onReceivedHttpAuthRequest(view, handler, host, realm);
        }

        @Override
        public void onLoadResource(WebView view, String url) {
            if (listener != null) {
                listener.onLoadResource(webView, url);
                return;
            }
            super.onLoadResource(view, url);
        }

        @Override
        public void onPageCommitVisible(WebView view, String url) {
            if (listener != null) {
                listener.onPageCommitVisible(webView, url);
                return;
            }
            super.onPageCommitVisible(view, url);
        }

        @Nullable
        @Override
        public WebResourceResponse shouldInterceptRequest(WebView view, String url) {
            if (listener != null) {
                return listener.shouldInterceptRequest(webView, url);
            }
            return super.shouldInterceptRequest(view, url);
        }

        @Override
        public void onTooManyRedirects(WebView view, Message cancelMsg, Message continueMsg) {
            if (listener != null) {
                listener.onTooManyRedirects(webView, cancelMsg, continueMsg);
                return;
            }
            super.onTooManyRedirects(view, cancelMsg, continueMsg);
        }

        @Override
        public void onReceivedHttpError(WebView view, WebResourceRequest request, WebResourceResponse errorResponse) {
            if (listener != null) {
                listener.onReceivedHttpError(webView, request, errorResponse);
                return;
            }
            super.onReceivedHttpError(view, request, errorResponse);
        }

        @Override
        public void onFormResubmission(WebView view, Message dontResend, Message resend) {
            if (listener != null) {
                listener.onFormResubmission(webView, dontResend, resend);
                return;
            }
            super.onFormResubmission(view, dontResend, resend);
        }

        @Override
        public void doUpdateVisitedHistory(WebView view, String url, boolean isReload) {
            if (listener != null) {
                listener.doUpdateVisitedHistory(webView, url, isReload);
                return;
            }
            super.doUpdateVisitedHistory(view, url, isReload);
        }

        @Override
        public void onReceivedSslError(WebView view, SslErrorHandler handler, SslError error) {
            if (listener != null) {
                listener.onReceivedSslError(webView, handler, error);
                return;
            }
            super.onReceivedSslError(view, handler, error);
        }

        @Override
        public void onReceivedClientCertRequest(WebView view, ClientCertRequest request) {
            if (listener != null) {
                listener.onReceivedClientCertRequest(webView, request);
                return;
            }
            super.onReceivedClientCertRequest(view, request);
        }

        @Override
        public boolean shouldOverrideKeyEvent(WebView view, KeyEvent event) {
            if (listener != null) {
                return listener.shouldOverrideKeyEvent(webView, event);
            }
            return super.shouldOverrideKeyEvent(view, event);
        }

        @Override
        public void onScaleChanged(WebView view, float oldScale, float newScale) {
            if (listener != null) {
                listener.onScaleChanged(webView, oldScale, newScale);
                return;
            }
            super.onScaleChanged(view, oldScale, newScale);
        }

        @Override
        public void onReceivedLoginRequest(WebView view, String realm, @Nullable String account, String args) {
            if (listener != null) {
                listener.onReceivedLoginRequest(webView, realm, account, args);
                return;
            }
            super.onReceivedLoginRequest(view, realm, account, args);
        }

        @Override
        public boolean onRenderProcessGone(WebView view, RenderProcessGoneDetail detail) {
            if (listener != null) {
                return listener.onRenderProcessGone(webView, detail);
            }
            return super.onRenderProcessGone(view, detail);
        }

        @Override
        public void onSafeBrowsingHit(WebView view, WebResourceRequest request, int threatType, SafeBrowsingResponse callback) {
            if (listener != null) {
                listener.onSafeBrowsingHit(webView, request, threatType, callback);
                return;
            }
            super.onSafeBrowsingHit(view, request, threatType, callback);
        }

    }


    private class myWebChromeClient extends WebChromeClient {
        WKWebView webView;
        Context mContext;
        WebClient listener;

        public myWebChromeClient(WKWebView webView, Context mContext, WebClient listener) {
            this.webView = webView;
            this.mContext = mContext;
            this.listener = listener;
        }

        @Override
        public void onProgressChanged(WebView view, int newProgress) {
            if (listener != null) {
                listener.onProgressChanged(webView, newProgress);
                return;
            }
            super.onProgressChanged(view, newProgress);
        }

        @Override
        public void onReceivedTitle(WebView view, String title) {
            if (listener != null) {
                listener.onReceivedTitle(webView, title);
                return;
            }
            super.onReceivedTitle(view, title);
        }

        @Override
        public void onReceivedIcon(WebView view, Bitmap icon) {
            if (listener != null) {
                listener.onReceivedIcon(webView, icon);
                return;
            }
            super.onReceivedIcon(view, icon);
        }

        @Override
        public void onReceivedTouchIconUrl(WebView view, String url, boolean precomposed) {
            if (listener != null) {
                listener.onReceivedTouchIconUrl(webView, url, precomposed);
                return;
            }
            super.onReceivedTouchIconUrl(view, url, precomposed);
        }

        @Override
        public void onShowCustomView(View view, CustomViewCallback callback) {
            if (listener != null) {
                listener.onShowCustomView(view, callback);
                return;
            }
            super.onShowCustomView(view, callback);
        }

        @Override
        public void onShowCustomView(View view, int requestedOrientation, CustomViewCallback callback) {
            if (listener != null) {
                listener.onShowCustomView(view, requestedOrientation, callback);
                return;
            }
            super.onShowCustomView(view, requestedOrientation, callback);
        }

        @Override
        public void onHideCustomView() {
            if (listener != null) {
                listener.onHideCustomView();
                return;
            }
            super.onHideCustomView();
        }

        @Override
        public boolean onCreateWindow(WebView view, boolean isDialog, boolean isUserGesture, Message resultMsg) {
            if (listener != null) {
                return listener.onCreateWindow(webView, isDialog, isUserGesture, resultMsg);
            }
            return super.onCreateWindow(view, isDialog, isUserGesture, resultMsg);
        }

        @Override
        public void onRequestFocus(WebView view) {
            if (listener != null) {
                listener.onRequestFocus(webView);
                return;
            }
            super.onRequestFocus(view);
        }

        @Override
        public void onCloseWindow(WebView window) {
            if (listener != null) {
                listener.onCloseWindow(webView);
                return;
            }
            super.onCloseWindow(window);
        }

        @Override
        public boolean onJsAlert(WebView view, String url, String message, JsResult result) {
            if (listener != null) {
                return listener.onJsAlert(webView, url, message, result);
            }
            return super.onJsAlert(view, url, message, result);
        }

        @Override
        public boolean onJsConfirm(WebView view, String url, String message, JsResult result) {
            if (listener != null) {
                return listener.onJsConfirm(webView, url, message, result);
            }
            return super.onJsConfirm(view, url, message, result);
        }

        @Override
        public boolean onJsPrompt(WebView view, String url, String message, String defaultValue, JsPromptResult result) {
            if (listener != null) {
                return listener.onJsPrompt(webView, url, message, defaultValue, result);
            }
            return super.onJsPrompt(view, url, message, defaultValue, result);
        }

        @Override
        public boolean onJsBeforeUnload(WebView view, String url, String message, JsResult result) {
            if (listener != null) {
                return listener.onJsBeforeUnload(webView, url, message, result);
            }
            return super.onJsBeforeUnload(view, url, message, result);
        }

        @Override
        public void onExceededDatabaseQuota(String url, String databaseIdentifier, long quota, long estimatedDatabaseSize, long totalQuota, WebStorage.QuotaUpdater quotaUpdater) {
            if (listener != null) {
                listener.onExceededDatabaseQuota(url, databaseIdentifier, quota, estimatedDatabaseSize, totalQuota, quotaUpdater);
                return;
            }
            super.onExceededDatabaseQuota(url, databaseIdentifier, quota, estimatedDatabaseSize, totalQuota, quotaUpdater);
        }

        @Override
        public void onGeolocationPermissionsShowPrompt(String origin, GeolocationPermissions.Callback callback) {
            if (listener != null) {
                listener.onGeolocationPermissionsShowPrompt(origin, callback);
                return;
            }
            super.onGeolocationPermissionsShowPrompt(origin, callback);
        }

        @Override
        public void onGeolocationPermissionsHidePrompt() {
            if (listener != null) {
                listener.onGeolocationPermissionsHidePrompt();
                return;
            }
            super.onGeolocationPermissionsHidePrompt();
        }

        @Override
        public void onPermissionRequest(PermissionRequest request) {
            if (listener != null) {
                listener.onPermissionRequest(request);
                return;
            }
            super.onPermissionRequest(request);
        }

        @Override
        public void onPermissionRequestCanceled(PermissionRequest request) {
            if (listener != null) {
                listener.onPermissionRequestCanceled(request);
                return;
            }
            super.onPermissionRequestCanceled(request);
        }

        @Override
        public boolean onJsTimeout() {
            if (listener != null) {
                return listener.onJsTimeout();
            }
            return super.onJsTimeout();
        }

        @Override
        public void onConsoleMessage(String message, int lineNumber, String sourceID) {
            if (listener != null) {
                listener.onConsoleMessage(message, lineNumber, sourceID);
                return;
            }
            super.onConsoleMessage(message, lineNumber, sourceID);
        }

        @Override
        public boolean onConsoleMessage(ConsoleMessage consoleMessage) {
            if (listener != null) {
                return listener.onConsoleMessage(consoleMessage);
            }
            return super.onConsoleMessage(consoleMessage);
        }

        @Nullable
        @Override
        public Bitmap getDefaultVideoPoster() {
            if (listener != null) {
                return listener.getDefaultVideoPoster();
            }
            return super.getDefaultVideoPoster();
        }

        @Nullable
        @Override
        public View getVideoLoadingProgressView() {
            if (listener != null) {
                return listener.getVideoLoadingProgressView();
            }
            return super.getVideoLoadingProgressView();
        }

        @Override
        public void getVisitedHistory(ValueCallback<String[]> callback) {
            if (listener != null) {
                listener.getVisitedHistory(callback);
                return;
            }
            super.getVisitedHistory(callback);
        }

        @Override
        public boolean onShowFileChooser(WebView view, ValueCallback<Uri[]> filePathCallback, FileChooserParams fileChooserParams) {
            if (listener != null) {
                return listener.onShowFileChooser(webView, filePathCallback, fileChooserParams);
            }
            return super.onShowFileChooser(view, filePathCallback, fileChooserParams);
        }

    }
}
