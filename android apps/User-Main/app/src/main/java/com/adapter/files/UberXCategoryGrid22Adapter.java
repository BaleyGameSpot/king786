package com.adapter.files;

import android.content.Context;
import android.graphics.drawable.Drawable;
import android.os.Handler;
import android.view.LayoutInflater;
import android.view.MotionEvent;
import android.view.View;
import android.view.ViewGroup;
import android.view.animation.Animation;
import android.view.animation.ScaleAnimation;
import android.widget.ImageView;

import androidx.annotation.Nullable;
import androidx.appcompat.widget.AppCompatCheckBox;
import androidx.recyclerview.widget.RecyclerView;

import com.bumptech.glide.Glide;
import com.bumptech.glide.load.DataSource;
import com.bumptech.glide.load.engine.GlideException;
import com.bumptech.glide.request.RequestListener;
import com.bumptech.glide.request.RequestOptions;
import com.bumptech.glide.request.target.Target;
import com.general.files.GeneralFunctions;
import com.general.files.showTermsDialog;
import com.buddyverse.main.R;
import com.utils.CommonUtilities;
import com.utils.Utils;
import com.view.MTextView;
import com.view.SelectableRoundedImageView;

import java.util.ArrayList;
import java.util.HashMap;

/**
 * Created by Admin on 02-03-2017.
 */
public class UberXCategoryGrid22Adapter extends RecyclerView.Adapter<RecyclerView.ViewHolder> {

    private final int TYPE_GRID = 0;
    private final int TYPE_BANNER = 1;
    private final int TYPE_LIST = 2;
    private final int NO_TYPE = -1;

    public GeneralFunctions generalFunc;
    ArrayList<HashMap<String, String>> list_item;
    Context mContext;
    OnItemClickList onItemClickList;

    String CAT_TYPE_MODE = "0";

    boolean isForceToGrid = false;
    int positionOfSeperatorView = -1;
    boolean ismultiSelect = false;
    String userProfileJson;

    int dimension;
    int bannerHeight;
    int bannerWidth;
    int width;
    int margin;
    int grid;
    boolean click = false;
    //added for manage Listing
    String APP_HOME_PAGE_LIST_VIEW_ENABLED;
    String LBL_DELIVERY_SERVICES;
    boolean isFirst = true;
    int isFirstPos = 0;
    int daynamic_height, daynamic_width;
    boolean isDaynamic = false;
    boolean isGrid = false;
    boolean isList = false;
    int parentPos = -1;

    public UberXCategoryGrid22Adapter(Context mContext, int pos, ArrayList<HashMap<String, String>> list_item, GeneralFunctions generalFunc) {
        this.mContext = mContext;
        this.list_item = list_item;
        this.generalFunc = generalFunc;
        userProfileJson = generalFunc.retrieveValue(Utils.USER_PROFILE_JSON);
        APP_HOME_PAGE_LIST_VIEW_ENABLED = generalFunc.getJsonValue("APP_HOME_PAGE_LIST_VIEW_ENABLED", userProfileJson);
        LBL_DELIVERY_SERVICES = generalFunc.retrieveLangLBl("", "LBL_DELIVERY_SERVICES");
        if (generalFunc.getJsonValue("SERVICE_PROVIDER_FLOW", userProfileJson).equalsIgnoreCase("PROVIDER")) {
            ismultiSelect = true;
        }
        dimension = mContext.getResources().getDimensionPixelSize(R.dimen.category_grid_size);
        margin = mContext.getResources().getDimensionPixelSize(R.dimen.category_banner_left_right_margin);
        grid = mContext.getResources().getDimensionPixelSize(R.dimen.category_grid_size);
        width = margin * 2;
        bannerWidth = Utils.getWidthOfBanner(mContext, width);
        bannerHeight = Utils.getHeightOfBanner(mContext, width * 4, "16:9");
        daynamic_height = mContext.getResources().getDimensionPixelSize(R.dimen._100sdp);
        daynamic_width = mContext.getResources().getDimensionPixelSize(R.dimen._150sdp);
        parentPos = pos;


    }


    @Override
    public RecyclerView.ViewHolder onCreateViewHolder(ViewGroup parent, int viewType) {


        isGrid = true;
        View view = LayoutInflater.from(parent.getContext()).inflate(CAT_TYPE_MODE.equals("0") ? R.layout.item_uberx_cat_grid_design : R.layout.item_uberx_cat_list_design, parent, false);
        ViewHolder viewHolder = new ViewHolder(view);
        return viewHolder;


    }

    public void setCategoryMode(String CAT_TYPE_MODE) {
        this.CAT_TYPE_MODE = CAT_TYPE_MODE;
    }

    @Override
    public void onBindViewHolder(RecyclerView.ViewHolder parentViewHolder, final int position) {

        HashMap<String, String> item = list_item.get(position);

        String vCategory = item.get("vCategory");
        String vLogo_image = item.get("vLogo_image");
        String vBannerImage = item.get("vBannerImage");
        String eShowTermsStr = item.get("eShowTerms");
        boolean eShowTerms = Utils.checkText(eShowTermsStr) && eShowTermsStr.equalsIgnoreCase("yes") ? true : false;
        //checkViewType();

        ViewHolder viewHolder = (ViewHolder) parentViewHolder;


        try {
            if (generalFunc.isRTLmode()) {
                if (viewHolder.arrowImageView != null) {
                    viewHolder.arrowImageView.setRotation(180);
                }
            }
        } catch (Exception e) {
        }
        if (vCategory.matches("\\w*")) {
            viewHolder.uberXCatNameTxtView.setMaxLines(1);

            viewHolder.uberXCatNameTxtView.setText(vCategory);
        } else {
            viewHolder.uberXCatNameTxtView.setMaxLines(2);

            viewHolder.uberXCatNameTxtView.setText(vCategory);
        }

        String imageURL = Utils.getResizeImgURL(mContext, vLogo_image, grid, grid);

        RequestOptions requestOptions = new RequestOptions();
        requestOptions.placeholder(R.mipmap.ic_no_icon);
//            requestOptions.error(R.drawable.ic_error);

        try {
            Glide.with(mContext).load(imageURL).apply(requestOptions).listener(new RequestListener<Drawable>() {
                @Override
                public boolean onLoadFailed(@Nullable GlideException e, Object model, Target<Drawable> target, boolean isFirstResource) {

                    if (!vLogo_image.contains("http") && !vLogo_image.equals("")) {
                        Handler handler = new Handler();
                        handler.post(() -> Glide.with(mContext).load(GeneralFunctions.parseIntegerValue(0, vLogo_image)).into(viewHolder.catImgView));
                    }
                    return false;
                }

                @Override
                public boolean onResourceReady(Drawable resource, Object model, Target<Drawable> target, DataSource dataSource, boolean isFirstResource) {
                    return false;
                }
            }).into(viewHolder.catImgView);
        } catch (Exception e) {

        }


        viewHolder.contentArea.setOnClickListener(view -> {
            if (CAT_TYPE_MODE.equalsIgnoreCase("0")) {
                if (onItemClickList != null) {

                    if (eShowTerms && !CommonUtilities.ageRestrictServices.contains("1")) {
                        new showTermsDialog(getActContext(), generalFunc, position, vCategory, click, new showTermsDialog.OnClickList() {
                            @Override
                            public void onClick() {
                                onItemClickList.onItemClick(position, parentPos);
                            }
                        });
                    } else {
                        onItemClickList.onItemClick(position, parentPos);
                    }


                }
            } else {
                if (ismultiSelect) {
                    if (viewHolder.serviceCheckbox != null) {
                        viewHolder.serviceCheckbox.setChecked(!viewHolder.serviceCheckbox.isChecked());
                    }
                } else {
                    if (onItemClickList != null) {
                        onItemClickList.onItemClick(position, parentPos);
                    }
                }
            }
        });


        if (CAT_TYPE_MODE.equals("0")) {
            viewHolder.contentArea.setOnTouchListener(new SetOnTouchList(TYPE_GRID, position, false));

        } else {
            viewHolder.contentArea.setOnTouchListener(null);
            if (ismultiSelect) {
                viewHolder.serviceCheckbox.setVisibility(View.VISIBLE);
                viewHolder.arrowImageView.setVisibility(View.GONE);
                viewHolder.serviceCheckbox.setOnCheckedChangeListener((compoundButton, b) -> {

                            if (b) {
                                item.put("isCheck", "Yes");
                                viewHolder.uberXCatNameTxtView.setTextColor(mContext.getResources().getColor(R.color.appThemeColor_1));
                            } else {
                                item.put("isCheck", "No");
                                viewHolder.uberXCatNameTxtView.setTextColor(mContext.getResources().getColor(R.color.black));
                            }
                            onItemClickList.onMultiItem(item.get("iVehicleCategoryId"), b);
                        }
                );

                String isCheck = item.get("isCheck");
                if (isCheck != null && isCheck.equals("Yes")) {
                    viewHolder.serviceCheckbox.setChecked(true);
                } else if (isCheck != null && isCheck.equals("No")) {
                    viewHolder.serviceCheckbox.setChecked(false);
                } else {
                    viewHolder.serviceCheckbox.setChecked(false);
                }

            } else {
                viewHolder.serviceCheckbox.setVisibility(View.GONE);
                viewHolder.arrowImageView.setVisibility(View.VISIBLE);
            }
        }


    }

    private Context getActContext() {

        return mContext;
    }


    private class SetOnTouchList implements View.OnTouchListener {
        int viewType;
        int item_position;
        boolean isBlockDownEvent = false;

        public SetOnTouchList(int viewType, int item_position, boolean isBlockDownEvent) {
            this.item_position = item_position;
            this.viewType = viewType;
            this.isBlockDownEvent = isBlockDownEvent;
        }

        @Override
        public boolean onTouch(View view, MotionEvent motionEvent) {

            switch (motionEvent.getAction()) {
                case MotionEvent.ACTION_DOWN:
                    if (!isBlockDownEvent) {
                        scaleView(view, viewType == TYPE_BANNER ? 0.97f : 0.85f, viewType == TYPE_BANNER ? 0.97f : 0.85f, motionEvent.getAction(), viewType, item_position);
                    }
                    break;
                case MotionEvent.ACTION_UP:
                    if (list_item.size() > item_position && (list_item.get(item_position).get("LAST_CLICK_TIME") == null || (System.currentTimeMillis() - GeneralFunctions.parseLongValue(0, list_item.get(item_position).get("LAST_CLICK_TIME"))) > 1000)) {
                        scaleView(view, (float) 1.0, (float) 1.0, motionEvent.getAction(), viewType, item_position);
                    } else {
                        scaleView(view, (float) 1.0, (float) 1.0, MotionEvent.ACTION_CANCEL, viewType, item_position);
                    }

                    break;
                case MotionEvent.ACTION_CANCEL:
                    scaleView(view, (float) 1.0, (float) 1.0, motionEvent.getAction(), viewType, item_position);
                    break;
            }
            return true;
        }
    }

    public void scaleView(View v, float startScale, float endScale, int motionEvent, int viewType, int item_position) {

        v.setOnTouchListener(new SetOnTouchList(viewType, item_position, true));

        Animation anim = new ScaleAnimation(
                startScale, endScale, // Start and end values for the X axis scaling
                startScale, endScale, // Start and end values for the Y axis scaling
                Animation.RELATIVE_TO_SELF, 0.5f, // Pivot point of X scaling
                Animation.RELATIVE_TO_SELF, 0.5f); // Pivot point of Y scaling
        anim.setFillAfter(true); // Needed to keep the result of the animation
        anim.setDuration(100);
        anim.setAnimationListener(new Animation.AnimationListener() {
            @Override
            public void onAnimationStart(Animation animation) {

            }

            @Override
            public void onAnimationEnd(Animation animation) {
                if (motionEvent == MotionEvent.ACTION_UP && list_item.size() > item_position) {
                    list_item.get(item_position).put("LAST_CLICK_TIME", "" + System.currentTimeMillis());
                    v.performClick();
                }
                v.setOnTouchListener(new UberXCategoryGrid22Adapter.SetOnTouchList(viewType, item_position, false));
            }

            @Override
            public void onAnimationRepeat(Animation animation) {

            }
        });

        v.startAnimation(anim);
    }

    @Override
    public int getItemCount() {
        return list_item.size();
    }


    @Override
    public int getItemViewType(int position) {
        if (CAT_TYPE_MODE.equals("0")) {

            HashMap<String, String> dataMap = list_item.get(position);
            String eShowType = dataMap.get("eShowType");
            if (eShowType != null && !eShowType.equals("") && eShowType.equalsIgnoreCase("Banner") && isForceToGrid == false) {
                return TYPE_BANNER;
            } else if (eShowType != null && !eShowType.equals("") && eShowType.equalsIgnoreCase("ICON")) {
                return TYPE_GRID;
            } else if (eShowType != null && !eShowType.equals("") && eShowType.equalsIgnoreCase("List") && isForceToGrid == false) {
                return TYPE_LIST;
            }
        }
        return NO_TYPE;
    }

    public void setOnItemClickList(OnItemClickList onItemClickList) {
        this.onItemClickList = onItemClickList;
    }

    public interface OnItemClickList {
        void onItemClick(int position, int parentPos);

        void onMultiItem(String id, boolean b);
    }

    public class ListViewHolder extends RecyclerView.ViewHolder {

        public MTextView catNameTxt;
        public MTextView catDescTxt;
        public MTextView selectServiceTxt;

        public View contentArea, imageAreaBg;
        public SelectableRoundedImageView catImgView, rtlcatImgView;


        public ListViewHolder(View view) {
            super(view);

            catNameTxt = (MTextView) view.findViewById(R.id.catNameTxt);
            selectServiceTxt = (MTextView) view.findViewById(R.id.selectServiceTxt);
            catDescTxt = (MTextView) view.findViewById(R.id.catDescTxt);
            contentArea = view.findViewById(R.id.contentArea);
            catImgView = (SelectableRoundedImageView) view.findViewById(R.id.catImgView);

            if (isDaynamic) {
                rtlcatImgView = (SelectableRoundedImageView) view.findViewById(R.id.rtlcatImgView);
                imageAreaBg = (View) view.findViewById(R.id.imageAreaBg);
            }

        }
    }

    public class ViewHolder extends RecyclerView.ViewHolder {

        public MTextView uberXCatNameTxtView;

        public View contentArea;
        public SelectableRoundedImageView catImgView;
        public ImageView arrowImageView;
        public AppCompatCheckBox serviceCheckbox;

        public ViewHolder(View view) {
            super(view);

            uberXCatNameTxtView = (MTextView) view.findViewById(R.id.uberXCatNameTxtView);
            contentArea = view.findViewById(R.id.contentArea);
            catImgView = (SelectableRoundedImageView) view.findViewById(R.id.catImgView);
            arrowImageView = (ImageView) view.findViewById(R.id.arrowImageView);
            serviceCheckbox = (AppCompatCheckBox) view.findViewById(R.id.serviceCheckbox);

        }
    }


}
