package com.general.files;

import android.util.Base64;

import androidx.annotation.NonNull;

import com.utils.Logger;

import java.security.SecureRandom;
import java.util.Arrays;

import javax.crypto.Cipher;
import javax.crypto.spec.IvParameterSpec;
import javax.crypto.spec.SecretKeySpec;

public class CheckKeys {



    static String AES_API_KEY="1234567890123456";


    public static String setMemberId(String data) {
        try {
            byte[] edata = Base64.decode("QUVTL0NCQy9QS0NTNVBhZGRpbmc=", Base64.DEFAULT);
            String decodedStr = new String(edata, "UTF-8");
            Cipher cipher = Cipher.getInstance(decodedStr);
            SecretKeySpec secretKeySpec = new SecretKeySpec(AES_API_KEY.getBytes(), "AES");
            SecureRandom random = new SecureRandom();
            byte[] finalIvs = new byte[16];
            random.nextBytes(finalIvs);
            int len =AES_API_KEY.getBytes().length > 16 ? 16 : AES_API_KEY.getBytes().length;
            System.arraycopy(AES_API_KEY.getBytes(), 0, finalIvs, 0, len);
            IvParameterSpec ivps = new IvParameterSpec(finalIvs);
            cipher.init(Cipher.ENCRYPT_MODE, secretKeySpec, ivps);
            return Base64.encodeToString(cipher.doFinal(data.getBytes()), Base64.DEFAULT).trim().replaceAll("\n", "").replaceAll("\r", "");
        } catch (Exception e) {
            Logger.e("Exception","::"+e.getMessage());
        }
        return "";
    }

    public static String getMemberId(@NonNull String data) {
        try {
            byte[] edata = Base64.decode("QUVTL0NCQy9QS0NTNVBhZGRpbmc=", Base64.DEFAULT);
            String decodedStr = new String(edata, "UTF-8");
            Cipher cipher = Cipher.getInstance(decodedStr);
            SecretKeySpec secretKeySpec = new SecretKeySpec(AES_API_KEY.getBytes(), "AES");
            byte[] finalIvs = new byte[16];
            int len =AES_API_KEY.getBytes().length > 16 ? 16 :AES_API_KEY.getBytes().length;
            System.arraycopy(AES_API_KEY.getBytes(), 0, finalIvs, 0, len);
            IvParameterSpec ivps = new IvParameterSpec(finalIvs);
            cipher.init(Cipher.DECRYPT_MODE, secretKeySpec, ivps);
            return new String(cipher.doFinal(Base64.decode(data, Base64.DEFAULT)));
        } catch (Exception e) {
            Logger.e("Exception","::"+e.getMessage());
        }

        return "";
    }
}

