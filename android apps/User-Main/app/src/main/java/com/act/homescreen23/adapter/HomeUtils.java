package com.act.homescreen23.adapter;

import android.content.Context;
import android.content.res.ColorStateList;
import android.graphics.Color;
import android.graphics.Typeface;
import android.graphics.drawable.GradientDrawable;
import android.text.Html;
import android.util.TypedValue;
import android.view.View;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.RelativeLayout;

import androidx.annotation.NonNull;
import androidx.core.content.ContextCompat;

import com.act.UberXHomeActivity;
import com.fontanalyzer.SystemFont;
import com.general.files.GeneralFunctions;
import com.google.android.material.imageview.ShapeableImageView;
import com.google.android.material.shape.CornerFamily;
import com.buddyverse.main.R;
import com.utils.LoadImage;
import com.utils.Utils;
import com.view.MTextView;

import org.json.JSONArray;
import org.json.JSONObject;

import java.util.ArrayList;

import javax.annotation.Nonnull;
import javax.annotation.Nullable;

public class HomeUtils {
    public static ArrayList<ArrayList<String>> getBidingAndServiceId(@Nonnull UberXHomeActivity mActivity, @Nullable JSONArray homeScreenDataArray) {
        ArrayList<ArrayList<String>> allIdList = new ArrayList<>();

        ArrayList<String> bidIDMap = new ArrayList<>();
        ArrayList<String> videoConsIDMap = new ArrayList<>();
        ArrayList<String> onDemandIDMap = new ArrayList<>();

        if (homeScreenDataArray != null) {
            for (int i = 0; i < homeScreenDataArray.length(); i++) {
                JSONObject tempObj_I = mActivity.generalFunc.getJsonObject(homeScreenDataArray, i);
                JSONArray imagesArr = mActivity.generalFunc.getJsonArray("imagesArr", tempObj_I);
                if (imagesArr != null) {
                    for (int k = 0; k < imagesArr.length(); k++) {
                        JSONObject tempObj_K = mActivity.generalFunc.getJsonObject(imagesArr, k);
                        JSONArray servicesArr = mActivity.generalFunc.getJsonArray("servicesArr", tempObj_K);
                        if (servicesArr != null) {
                            getServicesId(bidIDMap, videoConsIDMap, onDemandIDMap, mActivity, servicesArr);
                        }
                    }
                } else {
                    JSONArray servicesArr = mActivity.generalFunc.getJsonArray("servicesArr", tempObj_I);
                    if (servicesArr != null) {
                        getServicesId(bidIDMap, videoConsIDMap, onDemandIDMap, mActivity, servicesArr);
                    }
                }
            }
            allIdList.add(bidIDMap);
            allIdList.add(videoConsIDMap);
            allIdList.add(onDemandIDMap);
        }
        return allIdList;
    }

    public static void getServicesId(ArrayList<String> bidIDMap, ArrayList<String> videoConsIDMap, ArrayList<String> onDemandIDMap, @Nonnull UberXHomeActivity mActivity, @Nullable JSONArray servicesArr) {
        if (servicesArr != null) {
            for (int i = 0; i < servicesArr.length(); i++) {
                JSONObject tempObj = mActivity.generalFunc.getJsonObject(servicesArr, i);
                String eCatType = mActivity.generalFunc.getJsonValueStr("eCatType", tempObj);
                if (eCatType.equalsIgnoreCase("Bidding")) {
                    String iBiddingId = mActivity.generalFunc.getJsonValueStr("iBiddingId", tempObj);
                    if (!bidIDMap.contains(iBiddingId)) {
                        bidIDMap.add(iBiddingId);
                    }
                } else if (eCatType.equalsIgnoreCase("ServiceProvider")) {
                    String iVehicleCategoryId = mActivity.generalFunc.getJsonValueStr("iVehicleCategoryId", tempObj);

                    if (mActivity.generalFunc.getJsonValueStr("isVideoConsultEnable", tempObj).equalsIgnoreCase("Yes")) {
                        if (!videoConsIDMap.contains(iVehicleCategoryId)) {
                            videoConsIDMap.add(iVehicleCategoryId);
                        }
                    } else {
                        if (!onDemandIDMap.contains(iVehicleCategoryId)) {
                            onDemandIDMap.add(iVehicleCategoryId);
                        }
                    }

                } else if (tempObj != null && tempObj.has("moreCategories")) {
                    getServicesId(bidIDMap, videoConsIDMap, onDemandIDMap, mActivity, mActivity.generalFunc.getJsonArray("moreCategories", tempObj));
                }
            }
        }
    }

    public static void manageTextView(@Nonnull Context context, @Nonnull MTextView mTextView, @Nullable String sValue, @Nullable String vTitleFont, @Nonnull String vColor) {
        if (Utils.checkText(sValue)) {
            mTextView.setVisibility(View.VISIBLE);
            mTextView.setText(Html.fromHtml(sValue));
            if (Utils.checkText(vColor)) {
                mTextView.setTextColor(Color.parseColor(vColor));
            }

            mTextView.setTypeface(getStyle(vTitleFont));
            if (Utils.checkText(vTitleFont)) {
                float vFontSize = getSize(context, vTitleFont);
                if (vFontSize > 0) {
                    mTextView.setTextSize(TypedValue.COMPLEX_UNIT_PX, vFontSize);
                }
            }
        } else {
            mTextView.setVisibility(View.GONE);
        }
    }

    public static void setSpace(@Nonnull UberXHomeActivity mActivity, @Nonnull View layout, JSONObject layoutDetailsObj) {
        int left = 0, right = 0, top = 0, bottom = 0;
        if (mActivity.generalFunc.getJsonValueStr("topSpace", layoutDetailsObj).equalsIgnoreCase("Yes")) {
            top = mActivity.getResources().getDimensionPixelSize(R.dimen._10sdp);
        }
        if (mActivity.generalFunc.getJsonValueStr("bottomSpace", layoutDetailsObj).equalsIgnoreCase("Yes")) {
            bottom = mActivity.getResources().getDimensionPixelSize(R.dimen._10sdp);
        }

        if (mActivity.generalFunc.getJsonValueStr("leadingSpace", layoutDetailsObj).equalsIgnoreCase("Yes")) {
            left = mActivity.getResources().getDimensionPixelSize(R.dimen._15sdp);
        }
        if (mActivity.generalFunc.getJsonValueStr("trailingSpace", layoutDetailsObj).equalsIgnoreCase("Yes")) {
            right = mActivity.getResources().getDimensionPixelSize(R.dimen._15sdp);
        }

        layout.setPadding(left, top, right, bottom);

        String viewBgColor = mActivity.generalFunc.getJsonValueStr("viewBgColor", layoutDetailsObj);
        if (Utils.checkText(viewBgColor)) {
            layout.setBackgroundColor(Color.parseColor(viewBgColor));
        }
    }

    public static float getSize(@Nonnull Context context, @Nonnull String fValue) {
//        xxl = 22
//        xl = 20
//        lg = 18
//        xmd = 16
//        md = 14
//        sm = 12
//        xs = 10
//        xxs = 8

        if (fValue.contains("xxl")) {
            return context.getResources().getDimensionPixelSize(R.dimen._17ssp);
        } else if (fValue.contains("xl")) {
            return context.getResources().getDimensionPixelSize(R.dimen._15ssp);
        } else if (fValue.contains("lg")) {
            return context.getResources().getDimensionPixelSize(R.dimen._14ssp);
        } else if (fValue.contains("xmd")) {
            return context.getResources().getDimensionPixelSize(R.dimen._12ssp);
        } else if (fValue.contains("md")) {
            return context.getResources().getDimensionPixelSize(R.dimen._11ssp);
        } else if (fValue.contains("sm")) {
            return context.getResources().getDimensionPixelSize(R.dimen._10ssp);
        } else if (fValue.contains("xs")) {
            return context.getResources().getDimensionPixelSize(R.dimen._8ssp);
        } else if (fValue.contains("xxs")) {
            return context.getResources().getDimensionPixelSize(R.dimen._6ssp);
        } else {
            return context.getResources().getDimensionPixelSize(R.dimen._5ssp);
        }
    }

    public static Typeface getStyle(@Nullable String fValue) {
        if (fValue == null) {
            return SystemFont.FontStyle.DEFAULT.font;
        }
        if (fValue.contains("light")) {
            return SystemFont.FontStyle.LIGHT.font;
        } else if (fValue.contains("regular")) {
            return SystemFont.FontStyle.REGULAR.font;
        } else if (fValue.contains("medium")) {
            return SystemFont.FontStyle.MEDIUM.font;
        } else if (fValue.contains("semibold")) {
            return SystemFont.FontStyle.SEMI_BOLD.font;
        } else if (fValue.contains("bold")) {
            return SystemFont.FontStyle.BOLD.font;
        } else {
            return SystemFont.FontStyle.DEFAULT.font;
        }
    }

    public static void imageSizeWish(@Nonnull UberXHomeActivity mActivity, @Nonnull ShapeableImageView imgView, int placeholder, JSONObject layoutDetailsObj, JSONObject mServiceObject) {
        boolean isResize = false;
        int bWidth = 0, bHeight = 0;
        String imgSize = mActivity.generalFunc.getJsonValueStr("ImageSize", layoutDetailsObj);
        double ivRatio = GeneralFunctions.parseDoubleValue(0.0, mActivity.generalFunc.getJsonValueStr("vImageRatio", mServiceObject));

        if (imgSize.equalsIgnoreCase("sm")) {
            isResize = true;
            bWidth = mActivity.getResources().getDimensionPixelSize(R.dimen._40sdp);
        } else if (imgSize.equalsIgnoreCase("m")) {
            isResize = true;
            bWidth = mActivity.getResources().getDimensionPixelSize(R.dimen._55sdp);
        } else if (imgSize.equalsIgnoreCase("lg")) {
            isResize = true;
            bWidth = mActivity.getResources().getDimensionPixelSize(R.dimen._70sdp);
        }
        if (isResize) {
            bHeight = (int) (bWidth / ivRatio);

            LinearLayout.LayoutParams mParams = (LinearLayout.LayoutParams) imgView.getLayoutParams();
            mParams.width = bWidth;
            mParams.height = bHeight;
            imgView.setLayoutParams(mParams);
        }
        loadImg(mActivity, imgView, mActivity.generalFunc.getJsonValueStr("vImage", mServiceObject), placeholder, isResize, bWidth, bHeight);
    }

    public static void loadImg(@Nonnull Context mContext, @Nonnull View mImgView, @Nullable String Url, int placeholder, boolean isResize, int bWidth, int bHeight) {
        if (!Utils.checkText(Url)) {
            Url = "Temp";
        }
        if (isResize) {
            Url = Utils.getResizeImgURL(mContext, Url, bWidth, bHeight);
        }
        if (placeholder > 0) {
            new LoadImage.builder(LoadImage.bind(Url), (ImageView) mImgView).setPlaceholderImagePath(placeholder).setErrorImagePath(placeholder).build();
        } else {
            new LoadImage.builder(LoadImage.bind(Url), (ImageView) mImgView).build();
        }
    }

    public static int getExtraSpace(@Nonnull UberXHomeActivity mActivity, @Nullable JSONObject layoutDetailsObj, int bWidth, int spaceValue) {
        if (mActivity.generalFunc.getJsonValueStr("leadingSpace", layoutDetailsObj).equalsIgnoreCase("Yes")) {
            bWidth = bWidth - spaceValue;
        }
        if (mActivity.generalFunc.getJsonValueStr("trailingSpace", layoutDetailsObj).equalsIgnoreCase("Yes")) {
            bWidth = bWidth - spaceValue;
        }
        return bWidth;
    }

    public static void bannerBg(@Nonnull UberXHomeActivity mActivity, int pos, @Nonnull View cardView, @Nullable JSONObject layoutDetailsObj) {
        boolean isFullView = !mActivity.generalFunc.getJsonValueStr("leadingSpace", layoutDetailsObj).equalsIgnoreCase("Yes")
                && !mActivity.generalFunc.getJsonValueStr("trailingSpace", layoutDetailsObj).equalsIgnoreCase("Yes");

        String vBgColor = mActivity.generalFunc.getJsonValueStr(getKey("vBgColor", pos), layoutDetailsObj);
        if (isFullView) {
            cardView.setClipToOutline(false);
            if (Utils.checkText(vBgColor)) {
                cardView.setBackgroundColor(Color.parseColor(vBgColor));
            }
        } else {
            cardView.setClipToOutline(true);
            cardView.setBackground(ContextCompat.getDrawable(mActivity, R.drawable.card_view_23_gray_flat));
            if (Utils.checkText(vBgColor)) {
                cardView.setBackgroundTintList(ColorStateList.valueOf(Color.parseColor(vBgColor)));
            }
        }
    }

    public static void imgCorner(@Nonnull GeneralFunctions generalFunc, JSONObject layoutDetailsObj, @Nonnull ShapeableImageView mImgView, int allCorners) {
        if (generalFunc.getJsonValueStr("leadingSpace", layoutDetailsObj).equalsIgnoreCase("No")
                || generalFunc.getJsonValueStr("trailingSpace", layoutDetailsObj).equalsIgnoreCase("No")) {
            allCorners = 0;
        }
        mImgView.setShapeAppearanceModel(mImgView.getShapeAppearanceModel().toBuilder().setAllCorners(CornerFamily.ROUNDED, allCorners).build());
    }

    public static void imgRadiusAndBGShadow(@Nonnull UberXHomeActivity mActivity, @Nonnull ShapeableImageView mImgView, @Nonnull View imgBgArea, JSONObject mServiceObject, JSONObject layoutDetailsObj, int allCorners) {
        if (mActivity.generalFunc.getJsonValueStr("ImageRadius", mServiceObject).equalsIgnoreCase("Yes")) {
            mImgView.setShapeAppearanceModel(mImgView.getShapeAppearanceModel().toBuilder().setAllCorners(CornerFamily.ROUNDED, allCorners).build());
        }
        //--------------
        String showBackgroundShadow = mActivity.generalFunc.getJsonValueStr("showBackgroundShadow", mServiceObject);
        if (showBackgroundShadow.equalsIgnoreCase("Yes")) {
            imgBgArea.setBackground(ContextCompat.getDrawable(mActivity, R.drawable.card_view_23_white_shadow));
        } else {
            if (mActivity.generalFunc.getJsonValueStr("showIconBorder", layoutDetailsObj).equalsIgnoreCase("Yes")) {
                imgBgArea.setBackground(ContextCompat.getDrawable(mActivity, R.drawable.card_view_23_gray_border_delivery_only));
                /*String vBgColor = mActivity.generalFunc.getJsonValueStr("vBgColor", layoutDetailsObj);
                if (Utils.checkText(vBgColor)) {
                    GradientDrawable drawable = (GradientDrawable) imgBgArea.getBackground();
                    drawable.setColor(Color.parseColor(vBgColor));
                }*/
            } else {
                imgBgArea.setBackground(null);
            }
        }
    }

    public static void promotionalTagArea(@Nonnull UberXHomeActivity mActivity, @NonNull MTextView mTextView, JSONObject mBannerObject) {
        String vPromotionalTagTxt = mActivity.generalFunc.getJsonValueStr("vPromotionalTagTxt", mBannerObject);
        if (Utils.checkText(vPromotionalTagTxt)) {
            mTextView.setText(vPromotionalTagTxt);


            String vPromotionalTagTxtPosition = mActivity.generalFunc.getJsonValueStr("vPromotionalTagTxtPosition", mBannerObject);

            RelativeLayout.LayoutParams params = (RelativeLayout.LayoutParams) mTextView.getLayoutParams();
            int left = 0, right = 0, topBottom = mActivity.getResources().getDimensionPixelSize(R.dimen._2sdp);

            if (vPromotionalTagTxtPosition.equalsIgnoreCase("top-left")) {
                params.addRule(RelativeLayout.ALIGN_PARENT_TOP);
                params.addRule(RelativeLayout.ALIGN_PARENT_START);

                left = mActivity.getResources().getDimensionPixelSize(R.dimen._8sdp);
                right = mActivity.getResources().getDimensionPixelSize(R.dimen._7sdp);

            } else if (vPromotionalTagTxtPosition.equalsIgnoreCase("top-right")) {
                params.addRule(RelativeLayout.ALIGN_PARENT_TOP, RelativeLayout.TRUE);
                params.addRule(RelativeLayout.ALIGN_PARENT_END, RelativeLayout.TRUE);

                left = mActivity.getResources().getDimensionPixelSize(R.dimen._7sdp);
                right = mActivity.getResources().getDimensionPixelSize(R.dimen._8sdp);

            } else if (vPromotionalTagTxtPosition.equalsIgnoreCase("bottom-left")) {
                params.addRule(RelativeLayout.ALIGN_PARENT_BOTTOM, RelativeLayout.TRUE);
                params.addRule(RelativeLayout.ALIGN_PARENT_START, RelativeLayout.TRUE);

                left = mActivity.getResources().getDimensionPixelSize(R.dimen._8sdp);
                right = mActivity.getResources().getDimensionPixelSize(R.dimen._7sdp);

            } else if (vPromotionalTagTxtPosition.equalsIgnoreCase("bottom-right")) {
                params.addRule(RelativeLayout.ALIGN_PARENT_BOTTOM, RelativeLayout.TRUE);
                params.addRule(RelativeLayout.ALIGN_PARENT_END, RelativeLayout.TRUE);

                left = mActivity.getResources().getDimensionPixelSize(R.dimen._7sdp);
                right = mActivity.getResources().getDimensionPixelSize(R.dimen._8sdp);
            }
            mTextView.setLayoutParams(params);

            mTextView.setPadding(left, topBottom, right, topBottom);
        }
    }

    public static void btnArea(@Nonnull UberXHomeActivity mActivity, @NonNull View btnView, JSONObject layoutDetailsObj) {
        String btnBorderColor = mActivity.generalFunc.getJsonValueStr("btnBorderColor", layoutDetailsObj);
        String btnBgColor = mActivity.generalFunc.getJsonValueStr("btnBgColor", layoutDetailsObj);

        GradientDrawable drawable = (GradientDrawable) btnView.getBackground();
        if (Utils.checkText(btnBorderColor)) {
            drawable.setStroke(Utils.dipToPixels(mActivity, 1), Color.parseColor(btnBorderColor));
        }
        if (Utils.checkText(btnBgColor)) {
            drawable.setColor(Color.parseColor(btnBgColor));
            btnView.setBackgroundTintList(null);
        }
    }

    public static void mainArea(int pos, @Nonnull UberXHomeActivity mActivity, @NonNull View mainView, JSONObject layoutDetailsObj) {
        String vBordeColor = mActivity.generalFunc.getJsonValueStr(getKey("vBordeColor", pos), layoutDetailsObj);
        String vBgColor = mActivity.generalFunc.getJsonValueStr(getKey("vBgColor", pos), layoutDetailsObj);

        GradientDrawable drawable = (GradientDrawable) mainView.getBackground();
        if (Utils.checkText(vBordeColor)) {
            drawable.setStroke(Utils.dipToPixels(mActivity, 1), Color.parseColor(vBordeColor));
        }
        if (Utils.checkText(vBgColor)) {
            drawable.setColor(Color.parseColor(vBgColor));
            mainView.setBackgroundTintList(null);
        }
    }

    public static String getKey(@Nonnull String key, int pos) {
        if (pos != 0) {
            key = key + (pos + 1);
        }
        return key;
    }

    public static void itemSpace(@Nonnull GeneralFunctions generalFunc, @Nonnull View view, boolean isGrid, boolean isFirst, boolean isLast, int sdp, int topSpace, int bottomSpace) {
        int left = 0, right = 0;
        if (isFirst) {
            right = isGrid ? sdp : sdp / 2;
        } else if (isLast) {
            left = isGrid ? sdp : sdp / 2;
        } else {
            right = sdp / 2;
            left = sdp / 2;
        }

        if (isFirst && isLast) {
            view.setPadding(0, 0, 0, 0);
        } else {
            if (generalFunc.isRTLmode()) {
                view.setPadding(right, topSpace, left, bottomSpace);
            } else {
                view.setPadding(left, topSpace, right, bottomSpace);
            }
        }
    }

    public static void imageSpace(@Nonnull UberXHomeActivity mActivity, @Nonnull View sImgView, JSONObject layoutDetailsObj) {
        String top = mActivity.generalFunc.getJsonValueStr("imageTopPadding", layoutDetailsObj);
        String bottom = mActivity.generalFunc.getJsonValueStr("imageBottomPadding", layoutDetailsObj);
        String left = mActivity.generalFunc.getJsonValueStr("imageLeftPadding", layoutDetailsObj);
        String right = mActivity.generalFunc.getJsonValueStr("imageRightPadding", layoutDetailsObj);
        sImgView.setPadding(getDP(mActivity, left), getDP(mActivity, top), getDP(mActivity, right), getDP(mActivity, bottom));

        //
        String mTop = mActivity.generalFunc.getJsonValueStr("imageTopMargin", layoutDetailsObj);
        String mBottom = mActivity.generalFunc.getJsonValueStr("imageBottomMargin", layoutDetailsObj);
        String mLeft = mActivity.generalFunc.getJsonValueStr("imageLeftMargin", layoutDetailsObj);
        String mRight = mActivity.generalFunc.getJsonValueStr("imageRightMargin", layoutDetailsObj);
        LinearLayout.LayoutParams params = (LinearLayout.LayoutParams) sImgView.getLayoutParams();
        params.setMargins(getDP(mActivity, mLeft), getDP(mActivity, mTop), getDP(mActivity, mRight), getDP(mActivity, mBottom));
        sImgView.setLayoutParams(params);
    }

    public static void textAreaSpace(@Nonnull UberXHomeActivity mActivity, @Nonnull View llTextArea, JSONObject mBannerObject, JSONObject layoutDetailsObj, boolean isLeft) {
        int displayCount = GeneralFunctions.parseIntegerValue(0, mActivity.generalFunc.getJsonValueStr("displayCount", layoutDetailsObj));
        boolean isFull = mActivity.generalFunc.getJsonValueStr("leadingSpace", layoutDetailsObj).equalsIgnoreCase("No")
                && mActivity.generalFunc.getJsonValueStr("trailingSpace", layoutDetailsObj).equalsIgnoreCase("No");

        if (mActivity.generalFunc.isRTLmode()) {
            isLeft = !isLeft;
        }

        int topBottom = mActivity.getResources().getDimensionPixelSize(R.dimen._4sdp);
        int leftRight = mActivity.getResources().getDimensionPixelSize(R.dimen._5sdp);
        if (isFull) {
            leftRight = mActivity.getResources().getDimensionPixelSize(R.dimen._15sdp);
        } else {
            if (displayCount == 1) {
                leftRight = leftRight * 2;
            }
        }

        int left = 0;
        if (GeneralFunctions.parseDoubleValue(0.0, mActivity.generalFunc.getJsonValueStr("vImageRatio", mBannerObject)) == 0.0) {
            left = mActivity.getResources().getDimensionPixelSize(R.dimen._2sdp);
        }

        if (isLeft) {
            llTextArea.setPadding(left, topBottom, leftRight, topBottom);
        } else {
            llTextArea.setPadding(leftRight, topBottom, left, topBottom);
        }
    }

    public static int getDP(@Nonnull UberXHomeActivity mActivity, String dpValue) {
        int dp = GeneralFunctions.parseIntegerValue(0, dpValue);
        if (dp == 1) {
            dp = mActivity.getResources().getDimensionPixelSize(R.dimen._1sdp);
        } else if (dp == 2) {
            dp = mActivity.getResources().getDimensionPixelSize(R.dimen._2sdp);
        } else if (dp == 3) {
            dp = mActivity.getResources().getDimensionPixelSize(R.dimen._2sdp);
        } else if (dp == 4) {
            dp = mActivity.getResources().getDimensionPixelSize(R.dimen._3sdp);
        } else if (dp == 5) {
            dp = mActivity.getResources().getDimensionPixelSize(R.dimen._4sdp);
        } else if (dp == 6) {
            dp = mActivity.getResources().getDimensionPixelSize(R.dimen._5sdp);
        } else if (dp == 7) {
            dp = mActivity.getResources().getDimensionPixelSize(R.dimen._5sdp);
        } else if (dp == 8) {
            dp = mActivity.getResources().getDimensionPixelSize(R.dimen._6sdp);
        } else if (dp == 9) {
            dp = mActivity.getResources().getDimensionPixelSize(R.dimen._7sdp);
        } else if (dp == 10) {
            dp = mActivity.getResources().getDimensionPixelSize(R.dimen._8sdp);
        } else if (dp == 11) {
            dp = mActivity.getResources().getDimensionPixelSize(R.dimen._9sdp);
        } else if (dp == 12) {
            dp = mActivity.getResources().getDimensionPixelSize(R.dimen._9sdp);
        } else if (dp == 13) {
            dp = mActivity.getResources().getDimensionPixelSize(R.dimen._10sdp);
        } else if (dp == 14) {
            dp = mActivity.getResources().getDimensionPixelSize(R.dimen._11sdp);
        } else if (dp == 15) {
            dp = mActivity.getResources().getDimensionPixelSize(R.dimen._12sdp);
        } else if (dp == 16) {
            dp = mActivity.getResources().getDimensionPixelSize(R.dimen._13sdp);
        } else if (dp == 17) {
            dp = mActivity.getResources().getDimensionPixelSize(R.dimen._13sdp);
        } else if (dp == 18) {
            dp = mActivity.getResources().getDimensionPixelSize(R.dimen._14sdp);
        } else if (dp == 19) {
            dp = mActivity.getResources().getDimensionPixelSize(R.dimen._15sdp);
        } else if (dp == 20) {
            dp = mActivity.getResources().getDimensionPixelSize(R.dimen._15sdp);
        } else if (dp == 21) {
            dp = mActivity.getResources().getDimensionPixelSize(R.dimen._16sdp);
        } else if (dp == 22) {
            dp = mActivity.getResources().getDimensionPixelSize(R.dimen._17sdp);
        } else if (dp == 23) {
            dp = mActivity.getResources().getDimensionPixelSize(R.dimen._18sdp);
        } else if (dp == 24) {
            dp = mActivity.getResources().getDimensionPixelSize(R.dimen._19sdp);
        } else if (dp == 25) {
            dp = mActivity.getResources().getDimensionPixelSize(R.dimen._19sdp);
        } else if (dp == 26) {
            dp = mActivity.getResources().getDimensionPixelSize(R.dimen._20sdp);
        } else if (dp == 27) {
            dp = mActivity.getResources().getDimensionPixelSize(R.dimen._21sdp);
        } else if (dp == 28) {
            dp = mActivity.getResources().getDimensionPixelSize(R.dimen._22sdp);
        } else if (dp == 29) {
            dp = mActivity.getResources().getDimensionPixelSize(R.dimen._22sdp);
        } else if (dp == 30) {
            dp = mActivity.getResources().getDimensionPixelSize(R.dimen._23sdp);
        } else if (dp == 31) {
            dp = mActivity.getResources().getDimensionPixelSize(R.dimen._24sdp);
        } else if (dp == 32) {
            dp = mActivity.getResources().getDimensionPixelSize(R.dimen._25sdp);
        } else if (dp == 33) {
            dp = mActivity.getResources().getDimensionPixelSize(R.dimen._25sdp);
        } else if (dp == 34) {
            dp = mActivity.getResources().getDimensionPixelSize(R.dimen._26sdp);
        } else if (dp == 35) {
            dp = mActivity.getResources().getDimensionPixelSize(R.dimen._27sdp);
        } else if (dp == 36) {
            dp = mActivity.getResources().getDimensionPixelSize(R.dimen._28sdp);
        } else if (dp > 36) {
            dp = mActivity.getResources().getDimensionPixelSize(R.dimen._30sdp);
        }
        return dp;
    }
}