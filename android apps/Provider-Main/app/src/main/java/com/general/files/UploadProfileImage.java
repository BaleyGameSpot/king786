package com.general.files;

import static com.general.files.GeneralFunctions.rotateBitmap;

import android.app.Activity;
import android.content.Context;
import android.graphics.Bitmap;
import android.graphics.BitmapFactory;
import android.os.Build;
import android.text.TextUtils;

import androidx.annotation.NonNull;

import com.act.HailActivity;
import com.act.MyGalleryActivity;
import com.act.MyProfileActivity;
import com.act.UploadDocActivity;
import com.act.ViewMultiDeliveryDetailsActivity;
import com.act.WorkingtrekActivity;
import com.act.deliverAll.LiveTrackOrderDetail2Activity;
import com.act.deliverAll.LiveTrackOrderDetailActivity;
import com.general.features.SafetyTools;
import com.buddyverse.providers.BuildConfig;
import com.service.server.AppClient;
import com.service.server.DataReqBody;
import com.service.server.ServerTask;
import com.service.utils.DefaultParams;
import com.utils.CommonUtilities;
import com.utils.Logger;
import com.utils.ScalingUtilities;
import com.utils.Utils;
import com.view.MyProgressDialog;

import java.io.File;
import java.io.FileNotFoundException;
import java.io.FileOutputStream;
import java.util.HashMap;
import java.util.Objects;

import okhttp3.MediaType;
import okhttp3.MultipartBody;
import okhttp3.RequestBody;
import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;

/**
 * Created by Admin on 08-07-2016.
 */
public class UploadProfileImage implements DataReqBody.UploadCallbacks {

    String selectedImagePath;
    String responseString = "";

    String temp_File_Name = "";
    HashMap<String, String> paramsList;
    String type = "";
    Activity act;
    MyProgressDialog myPDialog;
    GeneralFunctions generalFunc;

    boolean isProgressUpdateDialog = false;
    String txtMsg = "";
    OpenProgressUpdateDialog openProgressDialog;

    Call<Object> call_current;
    boolean isTaskKilled = false;

    public UploadProfileImage(Activity act, String selectedImagePath, String temp_File_Name, HashMap<String, String> paramsList, String type) {
        this.selectedImagePath = selectedImagePath;
        this.temp_File_Name = temp_File_Name;
        this.paramsList = paramsList;
        this.type = type;
        this.act = act;
        this.generalFunc = MyApp.getInstance().getGeneralFun(act);

        this.paramsList.putAll(DefaultParams.getInstance().getDefaultParams());
    }

    public UploadProfileImage(Activity act, String selectedImagePath, String temp_File_Name, HashMap<String, String> paramsList) {
        this.selectedImagePath = selectedImagePath;
        this.temp_File_Name = temp_File_Name;
        this.paramsList = paramsList;
        this.act = act;

        this.paramsList.putAll(DefaultParams.getInstance().getDefaultParams());
    }

    public void execute(boolean isProgressUpdateDialog, String txtMsg) {
        this.isProgressUpdateDialog = isProgressUpdateDialog;
        this.txtMsg = txtMsg;
        execute();
    }

    public void execute() {
        if (generalFunc == null) {
            generalFunc = MyApp.getInstance().getGeneralFun(act);
        }

        if (isProgressUpdateDialog) {
            openProgressDialog = new OpenProgressUpdateDialog(act, generalFunc, this, txtMsg);
            try {
                openProgressDialog.run();
            } catch (Exception e) {
                Logger.e("Exception", "::" + e.getMessage());
            }

        } else {
            myPDialog = new MyProgressDialog(act, false, generalFunc.retrieveLangLBl("Loading", "LBL_LOADING_TXT"));
            try {
                myPDialog.show();
            } catch (Exception e) {
                Logger.e("Exception", "::" + e.getMessage());
            }
        }

        String filePath = selectedImagePath;
        if (TextUtils.isEmpty(type)) {
            if (android.os.Build.VERSION.SDK_INT >= Build.VERSION_CODES.Q) {
                String var5 = null;
                Bitmap var6 = null;

                String path = selectedImagePath;
                int DESIREDWIDTH = Utils.ImageUpload_DESIREDWIDTH;
                int DESIREDHEIGHT = Utils.ImageUpload_DESIREDHEIGHT;
                String tempImgName = temp_File_Name;

                try {
                    int var7 = Utils.getExifRotation(path);
                    Bitmap var8 = ScalingUtilities.decodeFile(path, DESIREDWIDTH, DESIREDHEIGHT, ScalingUtilities.ScalingLogic.CROP);
                    if (var8.getWidth() <= DESIREDWIDTH && var8.getHeight() <= DESIREDHEIGHT) {
                        if (var8.getWidth() > var8.getHeight()) {
                            var6 = ScalingUtilities.createScaledBitmap(var8, var8.getHeight(), var8.getHeight(), ScalingUtilities.ScalingLogic.CROP);
                        } else {
                            var6 = ScalingUtilities.createScaledBitmap(var8, var8.getWidth(), var8.getWidth(), ScalingUtilities.ScalingLogic.CROP);
                        }
                    } else {
                        var6 = ScalingUtilities.createScaledBitmap(var8, DESIREDWIDTH, DESIREDHEIGHT, ScalingUtilities.ScalingLogic.CROP);
                    }

                    var6 = rotateBitmap(var6, var7);
                    String var9 = act.getExternalCacheDir().toString(); // NOSONAR
                    File var10 = new File(var9 + "/" + "TempImages");
                    if (!var10.exists()) {
                        var10.mkdir();
                    }

                    File var11 = new File(var10.getAbsolutePath(), tempImgName);
                    var5 = var11.getAbsolutePath();
                    FileOutputStream var12;

                    try {
                        var12 = new FileOutputStream(var11); // NOSONAR
                        var6.compress(Bitmap.CompressFormat.JPEG, 60, var12);
                        var12.flush();
                        var12.close();
                    } catch (Exception var15) {
                        Logger.e("Exception", "::" + var15.getMessage());
                    }

                    var6.recycle();
                } catch (Throwable var16) {
                    Logger.e("", "" + var16.getMessage());
                }

                filePath = var5 == null ? path : var5;
            } else {
                filePath = generalFunc.decodeFile(selectedImagePath, Utils.ImageUpload_DESIREDWIDTH, Utils.ImageUpload_DESIREDHEIGHT, temp_File_Name);
            }
        } else if (Utils.checkText(type)) {
            if (type.equalsIgnoreCase("uploadImageWithMask")
                    || type.equalsIgnoreCase("after")
                    || type.equalsIgnoreCase("before")) {

                String path = selectedImagePath, absolutePath = null;

                try {
                    File file = new File(act.getExternalCacheDir().toString() + "/" + "TempImages"); // NOSONAR
                    if (!file.exists()) {
                        file.mkdir();
                    }
                    File tempFile = new File(file.getAbsolutePath(), temp_File_Name);
                    long imgSize = file.length() / 1024;
                    if (3 < imgSize) {
                        absolutePath = tempFile.getAbsolutePath();
                        Bitmap bitmapImage = BitmapFactory.decodeFile(selectedImagePath);
                        bitmapImage = rotateBitmap(bitmapImage, Utils.getExifRotation(path));
                        try {
                            FileOutputStream opStream = new FileOutputStream(tempFile); // NOSONAR
                            if (imgSize <= 4) {
                                bitmapImage.compress(Bitmap.CompressFormat.JPEG, 80, opStream);
                            } else if (imgSize <= 5) {
                                bitmapImage.compress(Bitmap.CompressFormat.JPEG, 75, opStream);
                            } else if (imgSize <= 6) {
                                bitmapImage.compress(Bitmap.CompressFormat.JPEG, 70, opStream);
                            } else {
                                bitmapImage.compress(Bitmap.CompressFormat.JPEG, 60, opStream);
                            }
                            opStream.flush();
                            opStream.close();
                            bitmapImage.recycle();
                        } catch (Exception e) {
                            Logger.e("Exception", "::" + e.getMessage());
                        }
                    }
                } catch (Exception e) {
                    Logger.e("Exception", "::" + e.getMessage());
                }
                filePath = absolutePath == null ? selectedImagePath : absolutePath;
            }
        }

        if (filePath.equals("")) {

            if (call_current != null) {
                call_current.cancel();
                call_current = null;
            }

            Call<Object> call = AppClient.getClient("POST", CommonUtilities.SERVER).getResponse(CommonUtilities.SERVER_WEBSERVICE_PATH, this.paramsList);
            call_current = call;
            call.enqueue(new Callback<>() {
                @Override
                public void onResponse(@NonNull Call<Object> call, @NonNull Response<Object> response) {
                    if (isTaskKilled) {
                        return;
                    }
                    if (response.isSuccessful()) {
                        responseString = AppClient.getGSONBuilder().toJson(response.body());
                        fireResponse();
                    } else {
                        responseString = "";
                        fireResponse();
                    }
                }

                @Override
                public void onFailure(@NonNull Call<Object> call, @NonNull Throwable t) {
                    if (isTaskKilled) {
                        return;
                    }
                    Logger.d("DataError", "::" + t.getMessage());
                    responseString = "";
                    fireResponse();
                }
            });
            return;
        }

        File file = new File(filePath);

        MultipartBody.Part filePart = null;
        if (!file.getAbsolutePath().equals("")) {
            if (type.equalsIgnoreCase("UploadServiceSafetyMedia")) {
                filePart = MultipartBody.Part.createFormData("safetyMessageFile", temp_File_Name, RequestBody.create(MediaType.parse("multipart/form-data"), file));
            } else if (type.equalsIgnoreCase("uploadImageWithMask")) {
                filePart = MultipartBody.Part.createFormData("vFaceMaskVerifyImage", temp_File_Name, new DataReqBody(MediaType.parse("multipart/form-data"), file, this));
            } else {
                filePart = MultipartBody.Part.createFormData("vImage", temp_File_Name, new DataReqBody(MediaType.parse("multipart/form-data"), file, this));
            }
        }

        HashMap<String, RequestBody> dataParams = new HashMap<>();

        for (String key : this.paramsList.keySet()) {
            dataParams.put(key, RequestBody.create(MediaType.parse("text/plain"), Objects.requireNonNull(this.paramsList.get(key))));
        }

        dataParams.put("tSessionId", RequestBody.create(MediaType.parse("text/plain"), generalFunc.getMemberId().equals("") ? "" : generalFunc.retrieveValue(Utils.SESSION_ID_KEY)));
        dataParams.put("deviceHeight", RequestBody.create(MediaType.parse("text/plain"), Utils.getScreenPixelHeight(act) + ""));
        dataParams.put("deviceWidth", RequestBody.create(MediaType.parse("text/plain"), Utils.getScreenPixelWidth(act) + ""));
        dataParams.put("GeneralUserType", RequestBody.create(MediaType.parse("text/plain"), Utils.app_type));
        dataParams.put("GeneralMemberId", RequestBody.create(MediaType.parse("text/plain"), generalFunc.getMemberId()));
        dataParams.put("GeneralDeviceType", RequestBody.create(MediaType.parse("text/plain"), "" + Utils.deviceType));
        dataParams.put("GeneralAppVersion", RequestBody.create(MediaType.parse("text/plain"), BuildConfig.VERSION_NAME));
        dataParams.put("vTimeZone", RequestBody.create(MediaType.parse("text/plain"), generalFunc.getTimezone()));
        dataParams.put("vUserDeviceCountry", RequestBody.create(MediaType.parse("text/plain"), Utils.getUserDeviceCountryCode(act)));
        dataParams.put("APP_TYPE", RequestBody.create(MediaType.parse("text/plain"), ServerTask.CUSTOM_APP_TYPE));

        Call<Object> call = AppClient.getClient("POST", CommonUtilities.SERVER).uploadData(CommonUtilities.SERVER_WEBSERVICE_PATH, filePart, dataParams);
        call_current = call;

        call.enqueue(new Callback<>() {

            @Override
            public void onResponse(@NonNull Call<Object> call, @NonNull Response<Object> response) {
                if (isTaskKilled) {
                    return;
                }
                if (response.isSuccessful()) {
                    responseString = AppClient.getGSONBuilder().toJson(response.body());
                    fireResponse();
                } else {
                    responseString = "";
                    fireResponse();
                }
            }

            @Override
            public void onFailure(@NonNull Call<Object> call, @NonNull Throwable t) {
                if (isTaskKilled) {
                    return;
                }
                Logger.d("DataError", "::" + t.getMessage());
                responseString = "";
                fireResponse();
            }
        });

    }

    private void fireResponse() {

        if (isTaskKilled || (act != null && MyApp.getInstance().getCurrentAct().isFinishing())) {
            return;
        }
        try {
            if (myPDialog != null) {
                myPDialog.close();
            }
            if (openProgressDialog != null) {
                openProgressDialog.dialog_img_update.cancel();
            }
            openProgressDialog = null;
        } catch (Exception e) {
            Logger.e("Exception", "::" + e.getMessage());
        }

        String message = Utils.checkText(responseString) ? generalFunc.getJsonValue(Utils.message_str, responseString) : null;

        if (MyApp.getInstance().validateApiResponse(responseString)) {
            return;
        }

        if (message != null && message.equals("SESSION_OUT")) {
            MyApp.getInstance().notifySessionTimeOut();
            Utils.runGC();
            return;
        }

        if (type.equalsIgnoreCase("UploadServiceSafetyMedia")) {
            SafetyTools.getInstance().handleImgUploadResponse(responseString);

        } else if (act instanceof MyProfileActivity activity) {
            activity.handleImgUploadResponse(responseString);
        } else if (act instanceof WorkingtrekActivity activity) {
            activity.handleImgUploadResponse(responseString, type);
        } else if (act instanceof UploadDocActivity activity) {
            activity.handleImgUploadResponse(responseString);
        } else if (act instanceof ViewMultiDeliveryDetailsActivity activity) {
            activity.handleImgUploadResponse(responseString, type);
        } else if (act instanceof LiveTrackOrderDetailActivity activity) {
            activity.handleImgUploadResponse(responseString, type);
        } else if (act instanceof LiveTrackOrderDetail2Activity activity) {
            activity.handleImgUploadResponse(responseString, type);
        } else if (act instanceof MyGalleryActivity activity) {
            activity.handleImgUploadResponse(responseString, type);
        } else if (act instanceof HailActivity activity) {
            activity.handleImgUploadResponse(responseString, type);
        }
    }

    @Override
    public void onProgressUpdate(int i, DataReqBody DataReqBody) {
        if (openProgressDialog != null) {
            openProgressDialog.updateProgress(i);
        }
    }

    @Override
    public void onError(DataReqBody DataReqBody) {

    }

    @Override
    public void onFinish(DataReqBody DataReqBody) {

    }

    @Override
    public void uploadStart(DataReqBody DataReqBody) {

    }

    public void cancel(boolean value) {

        this.isTaskKilled = value;
        if (call_current != null) {
            call_current.cancel();
        }
        try {
            if (myPDialog != null) {
                myPDialog.close();
            }
            if (openProgressDialog != null) {
                openProgressDialog.dialog_img_update.cancel();
            }
            openProgressDialog = null;
        } catch (Exception e) {
            Logger.e("Exception", "::" + e.getMessage());
        }
    }
}