package com.general.files;

import android.util.Base64;
import android.util.Log;

import com.utils.CommonUtilities;
import com.utils.Logger;

import java.nio.charset.Charset;
import java.security.spec.AlgorithmParameterSpec;
import java.util.HashMap;

import javax.crypto.Cipher;
import javax.crypto.spec.IvParameterSpec;
import javax.crypto.spec.SecretKeySpec;

public class AESEnDecryption {

    // Static variable reference of single_instance
    private static AESEnDecryption single_instance = null;
    // Static variable reference of secretKey
    public static String KEY_AES = "12345678911234567891123456789111";
    // Static variable reference of ivKey
    public static String IV_AES = "1234567891123456";

    public static AESEnDecryption getInstance() {
        if (single_instance == null)
            single_instance = new AESEnDecryption();

        return single_instance;
    }


    public String encrypt(String value) {
        try {

            return Base64.encodeToString(getCipherObj(true).doFinal(value.getBytes("UTF-8")), Base64.DEFAULT);
        } catch (Exception e) {
            Logger.e("Exception", "decrypt ::" + e.getMessage());
        }
        return null;
    }

    private Cipher getCipherObj(boolean isEncrypt) {

        try {
            byte[] key = KEY_AES.getBytes("UTF-8");
            byte[] ivs = IV_AES.getBytes("UTF-8");
            byte[] data = Base64.decode("QUVTL0NCQy9QS0NTN1BhZGRpbmcNCg==", Base64.DEFAULT);
            String decodedStr = new String(data, "UTF-8");
            Cipher cipher = Cipher.getInstance(decodedStr);
            SecretKeySpec secretKeySpec = new SecretKeySpec(key, "AES");
            AlgorithmParameterSpec paramSpec = new IvParameterSpec(ivs);


            if (isEncrypt)
                cipher.init(Cipher.ENCRYPT_MODE, secretKeySpec, paramSpec);
            else
                cipher.init(Cipher.DECRYPT_MODE, secretKeySpec, paramSpec);
            return cipher;
        } catch (Exception e) {
            Logger.e("Exception", "getCipherObj ::" + e.getMessage());
        }
        return null;

    }


    public String decrypt(String encrypted) {
        try {
            byte[] raw = Base64.decode(encrypted, Base64.DEFAULT);
            byte[] originalBytes = getCipherObj(false).doFinal(raw); //NOSONAR
            return new String(originalBytes, Charset.forName("UTF-8"));
        } catch (Exception e) {
            Logger.e("Exception", "decrypt ::" + e.getMessage());
        }
        return null;
    }

    public String decrypt(HashMap<String, String> hashMap) {
        try {
            KEY_AES = hashMap.get("AES_Key");
            IV_AES = hashMap.get("AES_Iv");
            byte[] raw = Base64.decode(hashMap.get("encryptedKey"), Base64.DEFAULT);
            byte[] originalBytes = getCipherObj(false).doFinal(raw); //NOSONAR
            return new String(originalBytes, Charset.forName("UTF-8"));
        } catch (Exception e) {
            Logger.e("Exception", "decrypt ::" + e.getMessage());
        }
        return null;
    }


    public HashMap<String, String> fetchKeyAndIVAnData(String str) {

        String key1 = "";
        String key2 = "";
        String mainStr = "";

        int OriginCnt = 0;
        int Key1Cnt = 0;
        int Key2Cnt = 0;
        int key1MainCnt = 0;
        int key2MainCnt = 0;
        int idNo = GeneralFunctions.parseIntegerValue(0, CommonUtilities.idNo) + 1;
        int i = 0;
        HashMap<String, String> map = new HashMap();
        for (char c : str.toCharArray()) {
            i++;
            if (OriginCnt == idNo) {

                //getting AES KEY 32 bits
                if (key1MainCnt < 8) {
                    if (Key1Cnt <= 4) {
                        key1 = key1 + c;
                        Key1Cnt++;
                        if (Key1Cnt == 4) {
                            key1MainCnt++;
                            OriginCnt = 1;
                            Key1Cnt = 0;
                            if (key1MainCnt == 8)
                                continue;
                        }
                    } else {
                        Key1Cnt = 1;
                        OriginCnt = 1;
                    }
                }

                //getting AES IV 16 bits
                if (key2MainCnt < 4 && key1MainCnt == 8) {
                    if (Key2Cnt <= 4) {
                        key2 = key2 + c;
                        Key2Cnt++;
                        if (Key2Cnt == 4) {
                            key2MainCnt++;
                            OriginCnt = 1;
                            Key2Cnt = 0;
                        }
                    } else {
                        Key2Cnt = 1;
                        OriginCnt = 1;
                    }
                }

                if (key1MainCnt == 8 && key2MainCnt == 4) {
                    Log.d("TEST MainStr: break", i + " " + str.toCharArray().length);
                    mainStr = mainStr + str.substring(i, str.toCharArray().length);
                    map.put("AES_Key", key1);
                    map.put("AES_Iv", key2);
                    map.put("encryptedKey", mainStr);
                    return map;
                }
            } else {
                OriginCnt++;
                mainStr = mainStr + c;
            }

        }
        Log.d("TEST AES_Key:", key1);
        Log.d("TEST AES_Iv:", key2);
        Log.d("TEST encryptedKey:", mainStr);
        return map;
    }

}
