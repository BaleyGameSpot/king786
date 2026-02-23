package com.general.files;

import android.app.KeyguardManager;
import android.content.Context;
import android.content.Intent;
import android.view.View;
import android.widget.CheckBox;
import android.widget.LinearLayout;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.biometric.BiometricManager;
import androidx.biometric.BiometricPrompt;
import androidx.core.content.ContextCompat;
import androidx.fragment.app.FragmentActivity;

import com.buddyverse.providers.R;
import com.utils.Logger;
import com.utils.Utils;

import org.json.JSONObject;

import java.util.HashMap;
import java.util.concurrent.Executor;

import static android.content.Context.KEYGUARD_SERVICE;
import static androidx.biometric.BiometricManager.Authenticators.BIOMETRIC_STRONG;
import static androidx.biometric.BiometricManager.Authenticators.BIOMETRIC_WEAK;

public class SmartLogin {

    private final Context mContext;
    private final GeneralFunctions generalFunc;
    private String isSmartLogin;
    private final JSONObject jsonObj_quickLogin;

    @Nullable
    public BiometricPrompt biometricPrompt;
    @Nullable
    public BiometricPrompt.PromptInfo promptInfo;

    @Nullable
    private OnEventListener mOnEventListener;
    @Nullable
    private CheckBox mCheckboxSmartLogin = null;

    public SmartLogin(Context context, GeneralFunctions generalFunc) {
        this.mContext = context;
        this.generalFunc = generalFunc;
        this.jsonObj_quickLogin = generalFunc.getJsonObject(generalFunc.retrieveValue("QUICK_LOGIN_DIC"));
        this.isSmartLogin = generalFunc.retrieveValue("isSmartLogin");
    }

    public SmartLogin(Context context, GeneralFunctions generalFunc, @Nullable OnEventListener listener) {
        this.mContext = context;
        this.generalFunc = generalFunc;
        this.mOnEventListener = listener;
        this.jsonObj_quickLogin = generalFunc.getJsonObject(generalFunc.retrieveValue("QUICK_LOGIN_DIC"));
        this.isSmartLogin = generalFunc.retrieveValue("isSmartLogin");
    }


    public void isSmartLoginEnable(LinearLayout llSmartLoginEnable, @Nullable CheckBox checkboxSmartLogin) {
        if (checkboxSmartLogin != null) {
            if (checkboxSmartLogin.isChecked()) {
                generalFunc.storeData("isSmartLogin", "Yes");
            } else {
                generalFunc.storeData("isSmartLogin", "No");
            }

        }

        if (mCheckboxSmartLogin == null) {
            mCheckboxSmartLogin = checkboxSmartLogin;
            if (generalFunc.retrieveValue("isSmartLoginEnable").equalsIgnoreCase("Yes")) {
                try {
                    llSmartLoginEnable.setVisibility(View.VISIBLE);
                    if (mCheckboxSmartLogin == null) {
                        return;
                    }
                    mCheckboxSmartLogin.setChecked(isSmartLogin.equalsIgnoreCase("Yes"));
                    mCheckboxSmartLogin.setOnCheckedChangeListener((buttonView, isChecked) -> {
                        if (isChecked) {
                            generalFunc.storeData("isSmartLogin", "Yes");
                        } else {
                            generalFunc.storeData("isSmartLogin", "No");
                        }
                        if (mOnEventListener != null) {
                            mOnEventListener.onResumeEvent();
                        }
                        Logger.d("isSmartLogin", "::2222::" + generalFunc.retrieveValue("isSmartLogin"));
                    });
                } catch (Exception e) {

                }
            } else {
                generalFunc.storeData("isSmartLogin", "No");
            }
        } else {
            isSmartLogin = generalFunc.retrieveValue("isSmartLogin");
            mCheckboxSmartLogin.setChecked(isSmartLogin.equalsIgnoreCase("Yes"));
        }
        onResume();
    }

    /*public void isSmartLoginEnable(LinearLayout llSmartLoginEnable,@Nullable CheckBox checkboxSmartLogin) {
        if (mCheckboxSmartLogin != null) {
            return;
        }
        mCheckboxSmartLogin = checkboxSmartLogin;
        if (generalFunc.retrieveValue("isSmartLoginEnable").equalsIgnoreCase("No")) {
            llSmartLoginEnable.setVisibility(View.VISIBLE);
            if (checkboxSmartLogin == null) {
                return;
            }
            checkboxSmartLogin.setChecked(isSmartLogin.equalsIgnoreCase("Yes"));
            checkboxSmartLogin.setOnCheckedChangeListener((buttonView, isChecked) -> {
                if (isChecked) {
                    generalFunc.storeData("isSmartLogin", "Yes");
                } else {
                    generalFunc.storeData("isSmartLogin", "No");
                }
                if (mOnEventListener != null) {
                    mOnEventListener.onResumeEvent();
                }
            });
        } else {
            llSmartLoginEnable.setVisibility(View.GONE);
            generalFunc.storeData("isSmartLogin", "No");
        }
    }*/

    public void onResume() {
        isSmartLogin = generalFunc.retrieveValue("isSmartLogin");
        if (isSmartLogin.equalsIgnoreCase("Yes")) {
            if (jsonObj_quickLogin != null) {
                BiometricData();
            }
        } else {
            if (biometricPrompt != null) {
                biometricPrompt.cancelAuthentication();
                biometricPrompt = null;
                promptInfo = null;
            }
        }

    }

    public boolean checkPrivacyAdded() {
        boolean isadded = false;
        if (BiometricManager.from(mContext).canAuthenticate(BIOMETRIC_WEAK | BIOMETRIC_STRONG) == BiometricManager.BIOMETRIC_SUCCESS) {
            isadded = true;
        } else if (BiometricManager.from(mContext).canAuthenticate(BIOMETRIC_WEAK | BIOMETRIC_STRONG) == BiometricManager.BIOMETRIC_STATUS_UNKNOWN
                || BiometricManager.from(mContext).canAuthenticate(BIOMETRIC_WEAK | BIOMETRIC_STRONG) == BiometricManager.BIOMETRIC_ERROR_NONE_ENROLLED) {
            KeyguardManager km = (KeyguardManager) mContext.getSystemService(KEYGUARD_SERVICE);
            if (km.isKeyguardSecure()) {
                isadded = true;
            }
        }
        return isadded;

    }


    private void BiometricData() {
        if (biometricPrompt == null) {
            Executor executor = ContextCompat.getMainExecutor(mContext);
            biometricPrompt = new BiometricPrompt((FragmentActivity) mContext, executor, new BiometricPrompt.AuthenticationCallback() {
                @Override
                public void onAuthenticationError(int errorCode, @NonNull CharSequence errString) {
                    super.onAuthenticationError(errorCode, errString);
                    if (errorCode == BiometricPrompt.ERROR_NO_DEVICE_CREDENTIAL) {
                        viewPasscode();
                    } else if (errorCode == BiometricPrompt.ERROR_NEGATIVE_BUTTON) {
                        viewPasscode();
                    } else if (errorCode == BiometricPrompt.ERROR_HW_NOT_PRESENT) {
                        viewPasscode();
                    } else if (errorCode == BiometricPrompt.ERROR_NO_BIOMETRICS) {
                        //Toast.makeText(mContext, "No any finger add", Toast.LENGTH_SHORT).show();
                        viewPasscode();
                    } else if (errorCode == BiometricPrompt.ERROR_USER_CANCELED) {
                        //Toast.makeText(mContext, "Fingerprint operation cancelled by user.", Toast.LENGTH_SHORT).show();
                    } else if (errorCode == BiometricPrompt.ERROR_LOCKOUT) {
                        //Toast.makeText(mContext, "Too many attempts. Try again later.", Toast.LENGTH_SHORT).show();
                        viewPasscode();
                    } else {
                        //Toast.makeText(mContext, "Authentication error: " + errString, Toast.LENGTH_SHORT).show();
                    }
                }

                @Override
                public void onAuthenticationSucceeded(@NonNull BiometricPrompt.AuthenticationResult result) {
                    super.onAuthenticationSucceeded(result);
                    //Toast.makeText(mContext, "Authentication succeeded!", Toast.LENGTH_SHORT).show();
                    if (mOnEventListener != null) {
                        mOnEventListener.onAuthenticationSucceeded();
                    }
                }

                @Override
                public void onAuthenticationFailed() {
                    super.onAuthenticationFailed();
                    //Toast.makeText(mContext, "Authentication failed", Toast.LENGTH_SHORT).show();
                }
            });

            String biometricLogin = generalFunc.retrieveLangLBl("", "LBL_BIOMETRIC_LOGIN_FOR_TXT") + mContext.getResources().getString(R.string.app_name);
            promptInfo = new BiometricPrompt.PromptInfo.Builder()
                    .setTitle(biometricLogin)
                    .setSubtitle(generalFunc.retrieveLangLBl("", "LBL_LOG_IN_USING_YOUR_BIOMETRIC_CREDENTIAL_TXT"))
                    .setNegativeButtonText(generalFunc.retrieveLangLBl("", "LBL_CANCEL_TXT"))
                    .setConfirmationRequired(false)
                    .setAllowedAuthenticators(BIOMETRIC_WEAK | BIOMETRIC_STRONG)
                    .build();
        }
    }

    private void viewPasscode() {
        if (biometricPrompt != null) {
            biometricPrompt.cancelAuthentication();
        }
        KeyguardManager km = (KeyguardManager) mContext.getSystemService(KEYGUARD_SERVICE);
        if (km.isKeyguardSecure()) {
            Intent i = km.createConfirmDeviceCredentialIntent(generalFunc.retrieveLangLBl("", "LBL_AUTHENTICATION_REQUIRED_TXT"), generalFunc.retrieveLangLBl("", "LBL_PASSWORD"));
            if (mOnEventListener != null) {
                mOnEventListener.onPasswordViewEvent(i);
            }
        } else {
            //Toast.makeText(mContext, "No any security setup done by user(pattern or password or pin or fingerprint", Toast.LENGTH_SHORT).show();
        }
    }


    public void clickEvent() {
        if (biometricPrompt != null && promptInfo != null) {
            biometricPrompt.authenticate(promptInfo); // NOSONAR
        }
    }

    public boolean ResultOk() {
        if (isSmartLogin.equalsIgnoreCase("Yes")) {
            if (jsonObj_quickLogin != null) {

                JSONObject userProfileJsonObj = generalFunc.getJsonObject(generalFunc.getJsonValueStr(Utils.USER_PROFILE_JSON, jsonObj_quickLogin));

                HashMap<String, String> storeData = new HashMap<>();
                storeData.put(Utils.iMemberId_KEY, generalFunc.getJsonValueStr(Utils.iMemberId_KEY, jsonObj_quickLogin));
                storeData.put(Utils.isUserLogIn, generalFunc.getJsonValueStr(Utils.isUserLogIn, jsonObj_quickLogin));
                storeData.put(Utils.SESSION_ID_KEY, generalFunc.getJsonValueStr("tSessionId", userProfileJsonObj));
                generalFunc.storeData(storeData);
                //signInUser(true);
                return true;
            }
        }
        return false;
    }

    public interface OnEventListener {
        void onPasswordViewEvent(Intent i);

        void onResumeEvent();

        void onAuthenticationSucceeded();
    }
}

