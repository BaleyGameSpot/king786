package com.adapter.files;

import android.annotation.SuppressLint;
import android.content.res.ColorStateList;
import android.view.Gravity;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.LinearLayout;
import android.widget.RelativeLayout;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.core.content.ContextCompat;
import androidx.recyclerview.widget.RecyclerView;

import com.activity.ParentActivity;
import com.general.files.GeneralFunctions;
import com.google.android.material.shape.CornerFamily;
import com.buddyverse.providers.R;
import com.buddyverse.providers.databinding.MessageBinding;
import com.utils.CommonUtilities;
import com.utils.LoadImage;
import com.utils.Utils;

import org.jetbrains.annotations.NotNull;

import java.util.ArrayList;
import java.util.HashMap;

public class ChatMessagesRecycleAdapter extends RecyclerView.Adapter<RecyclerView.ViewHolder> {

    private final ParentActivity act;
    private final GeneralFunctions generalFunc;
    @Nullable
    private final ArrayList<HashMap<String, String>> list_item;
    @Nullable
    private final OnItemClickListener mListener;
    private final boolean empty;
    @Nullable
    private MessageBinding msgBinding;

    public ChatMessagesRecycleAdapter(ParentActivity activity, @Nullable ArrayList<HashMap<String, String>> list_item, boolean empty, @Nullable OnItemClickListener listener) {
        this.act = activity;
        this.generalFunc = activity.generalFunc;
        this.list_item = list_item;
        this.empty = empty;
        this.mListener = listener;
    }

    @NonNull
    @Override
    public RecyclerView.ViewHolder onCreateViewHolder(@NotNull ViewGroup parent, int viewType) {
        return new ViewHolder(MessageBinding.inflate(LayoutInflater.from(parent.getContext()), parent, false));
    }

    @SuppressLint("SetTextI18n")
    @Override
    public void onBindViewHolder(@NonNull RecyclerView.ViewHolder holder, int position) {

        HashMap<String, String> item = list_item.get(position);

        if (holder instanceof ViewHolder vHolder) {

            String eUserType = item.get("iFromMemberType").toString();

            String viewType = item.get("isForPickupPhotoRequest");
            String vFile = item.get("vFile");
            if (Utils.checkText(viewType) && viewType.equalsIgnoreCase("Yes") && Utils.checkText(vFile)) {
                if (Utils.app_type.equalsIgnoreCase("Passenger")) {
                    eUserType = item.get("iToMemberType");
                    item.put("iFromMemberId", item.get("iToMemberId"));
                    item.put("iFromMemberImage", item.get("iToMemberImage"));
                } else {
                    eUserType = Utils.app_type;
                    item.put("iFromMemberId", generalFunc.getMemberId());
                    item.put("iFromMemberImage", generalFunc.getJsonValueStr(Utils.app_type.equalsIgnoreCase("Passenger") ? "vImgName" : "vImage", act.obj_userProfile));
                }
            }

            LinearLayout.LayoutParams paramsMain = new LinearLayout.LayoutParams(LinearLayout.LayoutParams.WRAP_CONTENT, LinearLayout.LayoutParams.WRAP_CONTENT);
            paramsMain.gravity = eUserType.equalsIgnoreCase(Utils.app_type) ? Gravity.END : Gravity.START;
            vHolder.binding.activityMain.setLayoutParams(paramsMain);
            vHolder.binding.activityMain.setBackgroundTintList(ContextCompat.getColorStateList(act, eUserType.equalsIgnoreCase(Utils.app_type) ? R.color.text23Pro_Dark : R.color.appThemeColor_1));

            vHolder.binding.leftUserImageview.setVisibility(View.GONE);
            vHolder.binding.rightuserImageview.setVisibility(View.GONE);

            vHolder.binding.leftshap.setVisibility(View.GONE);
            vHolder.binding.rightshape.setVisibility(View.GONE);

            vHolder.binding.leftMessageTime.setVisibility(View.GONE);
            vHolder.binding.rightMessageTime.setVisibility(View.GONE);

            String image_url;
            if (eUserType.equalsIgnoreCase("Company")) {
                image_url = CommonUtilities.COMPANY_PHOTO_PATH;
            } else if (eUserType.equalsIgnoreCase("Driver")) {
                image_url = CommonUtilities.PROVIDER_PHOTO_PATH;
            } else {
                image_url = CommonUtilities.USER_PHOTO_PATH;
            }
            image_url = image_url + item.get("iFromMemberId") + "/" + item.get("iFromMemberImage");

            if (eUserType.equalsIgnoreCase(Utils.app_type)) {

                vHolder.binding.leftshap.setVisibility(View.VISIBLE);
                vHolder.binding.leftshap.setColorFilter(ContextCompat.getColor(act, R.color.text23Pro_Dark));

                if (Utils.checkText(item.get("tTime"))) {
                    vHolder.binding.rightMessageTime.setVisibility(View.VISIBLE);
                    vHolder.binding.rightMessageTime.setText(generalFunc.convertNumberWithRTL(item.get("tTime")));
                }

                vHolder.binding.rightuserImageview.setVisibility(View.VISIBLE);
                new LoadImage.builder(LoadImage.bind(image_url), vHolder.binding.rightuserImageview).setErrorImagePath(R.mipmap.ic_no_pic_user).setPlaceholderImagePath(R.mipmap.ic_no_pic_user).build();

            } else {

                if (Utils.checkText(item.get("tTime"))) {
                    vHolder.binding.leftMessageTime.setVisibility(View.VISIBLE);
                    vHolder.binding.leftMessageTime.setText(generalFunc.convertNumberWithRTL(item.get("tTime")));
                }

                vHolder.binding.rightshape.setVisibility(View.VISIBLE);
                vHolder.binding.rightshape.setColorFilter(ContextCompat.getColor(act, R.color.appThemeColor_1));

                vHolder.binding.leftUserImageview.setVisibility(View.VISIBLE);
                new LoadImage.builder(LoadImage.bind(image_url), vHolder.binding.leftUserImageview).setErrorImagePath(R.mipmap.ic_no_pic_user).setPlaceholderImagePath(R.mipmap.ic_no_pic_user).build();
            }

            String tMessage = item.get("tMessage");
            if (Utils.checkText(tMessage)) {
                if (tMessage.length() == 1) {
                    vHolder.binding.messageText.setText(" " + tMessage + " ");
                } else {
                    vHolder.binding.messageText.setText(tMessage);
                }
            }

            photoImageView(position, vHolder, item);

        }

    }

    private void photoImageView(int position, ViewHolder vHolder, HashMap<String, String> item) {
        vHolder.binding.messageText.setVisibility(View.VISIBLE);
        vHolder.binding.imgArea.setVisibility(View.GONE);

        String viewType = item.get("isForPickupPhotoRequest");
        String vFile = item.get("vFile");
        if (Utils.checkText(viewType) && viewType.equalsIgnoreCase("Yes") || Utils.checkText(vFile)) {
            vHolder.binding.leftshap.setVisibility(View.GONE);
            vHolder.binding.rightshape.setVisibility(View.GONE);
            vHolder.binding.messageText.setVisibility(View.GONE);

            vHolder.binding.imgArea.setVisibility(View.VISIBLE);
            vHolder.binding.waitingView.setVisibility(View.GONE);

            vHolder.binding.titleTxt.setVisibility(View.GONE);
            vHolder.binding.subTitleTxt.setVisibility(View.GONE);
            vHolder.binding.uploadTxt.setVisibility(View.GONE);

            vHolder.binding.imgView.setPadding(0, 0, 0, 0);

            imageView(position, vHolder, item);
        }

        if (Utils.checkText(viewType) && viewType.equalsIgnoreCase("Yes")) {

            if (Utils.checkText(vFile)) {
                vHolder.binding.subTitleTxt.setVisibility(View.VISIBLE);
                vHolder.binding.subTitleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_PICKUP_LOC_PHOTO_TXT"));

            } else {

                int v15sdp = act.getResources().getDimensionPixelSize(R.dimen._15sdp);

                vHolder.binding.titleTxt.setVisibility(View.VISIBLE);
                vHolder.binding.titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_PICKUP_PHOTO_REQUESTED_TXT"));

                vHolder.binding.subTitleTxt.setVisibility(View.VISIBLE);

                String isExpired = item.get("isExpired");
                String eUserType = item.get("iFromMemberType").toString();
                if (eUserType.equalsIgnoreCase(Utils.app_type)) {
                    vHolder.binding.uploadTxt.setVisibility(View.GONE);

                    if (Utils.checkText(isExpired) && isExpired.equalsIgnoreCase("Yes")) {
                        vHolder.binding.waitingView.setVisibility(View.GONE);
                        vHolder.binding.titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_EXPIRED_TXT"));
                        vHolder.binding.subTitleTxt.setText("");
                        vHolder.binding.imgView.setImageResource(R.drawable.ic_expired);
                        vHolder.binding.imgView.setPadding(v15sdp, v15sdp, v15sdp, v15sdp);
                    } else {
                        vHolder.binding.waitingView.setVisibility(View.VISIBLE);
                        vHolder.binding.subTitleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_AWAITING_PICKUP_LOC_IMAGE_TXT"));
                    }

                } else {
                    vHolder.binding.uploadTxt.setVisibility(View.VISIBLE);
                    vHolder.binding.uploadTxt.setText(generalFunc.retrieveLangLBl("", "LBL_UPLOAD"));

                    if (empty && Utils.checkText(isExpired) && isExpired.equalsIgnoreCase("No")) {
                        msgBinding = vHolder.binding;
                    }
                    vHolder.binding.uploadTxt.setOnClickListener(v -> {
                        msgBinding = vHolder.binding;
                        if (mListener != null) {
                            mListener.onUploadClick(position);
                        }
                    });

                    if (Utils.checkText(isExpired) && isExpired.equalsIgnoreCase("Yes")) {
                        vHolder.binding.uploadTxt.setText(generalFunc.retrieveLangLBl("", "LBL_EXPIRED_TXT"));
                        vHolder.binding.uploadTxt.setEnabled(false);
                        vHolder.binding.uploadTxt.setBackgroundTintList(ColorStateList.valueOf(act.getResources().getColor(R.color.card_shadow)));

                        vHolder.binding.imgView.setImageResource(R.drawable.ic_expired);
                        vHolder.binding.imgView.setPadding(v15sdp, v15sdp, v15sdp, v15sdp);

                        vHolder.binding.subTitleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_PICKUP_LOC_PHOTO_TXT"));
                    } else {
                        vHolder.binding.uploadTxt.setBackgroundTintList(null);
                        vHolder.binding.uploadTxt.setEnabled(true);

                        String isOpenMediaDialog = item.get("isOpenMediaDialog");
                        if (Utils.checkText(isOpenMediaDialog) && isOpenMediaDialog.equalsIgnoreCase("Yes")) {
                            vHolder.binding.uploadTxt.performClick();
                            item.put("isOpenMediaDialog", "");
                        }

                        vHolder.binding.subTitleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_TAKE_PHOTO_OF_PICKUP_LOCATION_TXT"));
                    }

                    vHolder.binding.waitingView.setVisibility(View.GONE);
                }


            }
        }
    }

    private void imageView(int position, ViewHolder vHolder, HashMap<String, String> item) {
        vHolder.binding.imgView.setImageResource(R.drawable.ic_image_wait);
        vHolder.binding.imgView.setOnClickListener(null);

        int iWidth = (int) Utils.getScreenPixelWidth(act);
        iWidth = iWidth - act.getResources().getDimensionPixelSize(R.dimen._110sdp);

        String vFile = item.get("vFile");
        if (Utils.checkText(vFile)) {

            int vImageWidth = GeneralFunctions.parseIntegerValue(0, item.get("vImageWidth"));
            int vImageHeight = GeneralFunctions.parseIntegerValue(0, item.get("vImageHeight"));
            double iRatio = GeneralFunctions.parseDoubleValue(0.0, String.valueOf(vImageWidth)) / GeneralFunctions.parseDoubleValue(0.0, String.valueOf(vImageHeight));

            // inner Image Merging
            iWidth = iWidth - act.getResources().getDimensionPixelSize(R.dimen._14sdp);
            int iHeight = (int) (iWidth / iRatio);
            if (iRatio < 1) {
                iHeight = (int) (iWidth * iRatio);
            }

            RelativeLayout.LayoutParams imgParams = (RelativeLayout.LayoutParams) vHolder.binding.imgView.getLayoutParams();
            if (iRatio < 1) {
                imgParams.width = iHeight;
                imgParams.height = iWidth;

                LinearLayout.LayoutParams params = (LinearLayout.LayoutParams) vHolder.binding.activityMain.getLayoutParams();
                params.width = iHeight + act.getResources().getDimensionPixelSize(R.dimen._14sdp);
                vHolder.binding.activityMain.setLayoutParams(params);
            } else {
                imgParams.width = iWidth;
                imgParams.height = iHeight;
            }
            vHolder.binding.imgView.setLayoutParams(imgParams);

            String Url = Utils.getResizeImgURL(act, CommonUtilities.SERVER + vFile, imgParams.width, imgParams.height);
            new LoadImage.builder(LoadImage.bind(Url), vHolder.binding.imgView).setErrorImagePath(R.color.imageBg).setPlaceholderImagePath(R.color.imageBg).build();
            vHolder.binding.imgView.setShapeAppearanceModel(vHolder.binding.imgView.getShapeAppearanceModel().toBuilder().setAllCorners(CornerFamily.ROUNDED, act.getResources().getDimensionPixelSize(R.dimen._7sdp)).build());

            vHolder.binding.imgView.setOnClickListener(v -> {
                if (list_item != null && list_item.size() > 0) {
                    if (mListener != null) {
                        mListener.onImageViewClick(position, item);
                    }
                }
            });
        } else {
            RelativeLayout.LayoutParams imgParams = (RelativeLayout.LayoutParams) vHolder.binding.imgView.getLayoutParams();
            imgParams.width = iWidth;
            imgParams.height = act.getResources().getDimensionPixelSize(R.dimen._100sdp);
            vHolder.binding.imgView.setLayoutParams(imgParams);
        }
    }

    public void uploadView(boolean show, boolean error) {
        if (msgBinding != null) {
            if (show) {
                msgBinding.uploadTxt.setEnabled(false);
                msgBinding.uploadTxt.setBackgroundTintList(ColorStateList.valueOf(act.getResources().getColor(R.color.card_shadow)));
                msgBinding.waitingView.setVisibility(View.VISIBLE);
                msgBinding.titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_PICKUP_LOC_PHOTO_TXT"));
                msgBinding.subTitleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_PICKUP_PHOTO_UPLOADING_TXT"));
            } else {
                msgBinding.uploadTxt.setBackgroundTintList(null);
                msgBinding.uploadTxt.setEnabled(true);
                msgBinding.waitingView.setVisibility(View.GONE);
                if (error) {
                    msgBinding.titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_PICKUP_PHOTO_REQUESTED_TXT"));
                    msgBinding.subTitleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_PICKUP_LOC_PHOTO_TXT"));
                }
            }
        }
    }

    @Override
    public int getItemCount() {
        return list_item != null ? list_item.size() : 0;
    }

    protected static class ViewHolder extends RecyclerView.ViewHolder {

        private final MessageBinding binding;

        private ViewHolder(MessageBinding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }
    }

    public interface OnItemClickListener {
        void onImageViewClick(int selPos, HashMap<String, String> listItem);

        void onUploadClick(int position);
    }
}