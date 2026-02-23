package com.adapter.files;

import android.annotation.SuppressLint;
import android.content.Context;
import android.os.Looper;
import android.view.LayoutInflater;
import android.view.MotionEvent;
import android.view.View;
import android.view.ViewGroup;
import android.view.animation.Animation;
import android.view.animation.ScaleAnimation;
import android.widget.ImageView;
import android.widget.TextView;

import androidx.recyclerview.widget.GridLayoutManager;
import androidx.recyclerview.widget.RecyclerView;

import com.general.files.GeneralFunctions;
import com.general.files.showTermsDialog;
import com.buddyverse.main.R;
import com.utils.CommonUtilities;
import com.utils.LoadImage;
import com.utils.Logger;
import com.utils.Utils;
import com.view.MTextView;

import org.jetbrains.annotations.NotNull;
import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.HashMap;

public class UberXCategoryMoreAdapter extends RecyclerView.Adapter<RecyclerView.ViewHolder> {

    private static final int TYPE_HEADER = 0;
    private static final int TYPE_LIST = 1;

    private final GeneralFunctions generalFunc;
    private final Context mContext;
    private ArrayList<HashMap<String, String>> list_item;
    private OnItemClickList onItemClickList;

    private final int grid, parentPos, daynamic_height, daynamic_width;
    private int isFirstPos = 0;
    private boolean isFirst = true, isDaynamic = false;

    public UberXCategoryMoreAdapter(Context mContext, ArrayList<HashMap<String, String>> list_item, int parentPos, GeneralFunctions generalFunc) {
        this.mContext = mContext;
        this.list_item = list_item;
        this.parentPos = parentPos;
        this.generalFunc = generalFunc;
        String userProfileJson = generalFunc.retrieveValue(Utils.USER_PROFILE_JSON);
        if (generalFunc.getJsonValue("ENABLE_NEW_HOME_SCREEN_LAYOUT_APP", userProfileJson) != null && generalFunc.getJsonValue("ENABLE_NEW_HOME_SCREEN_LAYOUT_APP", userProfileJson).equalsIgnoreCase("Yes")) {
            this.isDaynamic = true;
        }
        this.grid = mContext.getResources().getDimensionPixelSize(R.dimen.category_grid_size);
        this.daynamic_height = mContext.getResources().getDimensionPixelSize(R.dimen._60sdp);
        this.daynamic_width = mContext.getResources().getDimensionPixelSize(R.dimen._60sdp);
    }

    @NotNull
    @Override
    public RecyclerView.ViewHolder onCreateViewHolder(@NotNull ViewGroup parent, int viewType) {
        View view;
        if (viewType == TYPE_HEADER) {
            view = LayoutInflater.from(parent.getContext()).inflate(R.layout.item_medical_service_section, parent, false);
            return new HeaderViewHolder(view);
        } else {
            if (isDaynamic) {
                view = LayoutInflater.from(parent.getContext()).inflate(R.layout.item_uberx_daynamiclist_design_more, parent, false);
            } else {
                view = LayoutInflater.from(parent.getContext()).inflate(R.layout.item_uberx_list_design, parent, false);
            }
            return new ListViewHolderHorizontalMore(view);
        }
    }

    @Override
    public void onBindViewHolder(@NotNull RecyclerView.ViewHolder parentViewHolder, final int position) {

        if (parentViewHolder instanceof ListViewHolderHorizontalMore) {
            HashMap<String, String> item = list_item.get(position);
            String vCategory = item.get("vCategory");
            String eShowTermsStr = item.get("eShowTerms");
            String vListLogo = item.get("vListLogo");
            boolean eShowTerms = Utils.checkText(eShowTermsStr) && eShowTermsStr.equalsIgnoreCase("yes");

            ListViewHolderHorizontalMore viewHolder = (ListViewHolderHorizontalMore) parentViewHolder;

            if (isFirstPos == position) {
                isFirst = true;
            }
            if (isFirst) {
                isFirst = false;
                isFirstPos = position;
            }
            viewHolder.catNameTxt.setText(vCategory);


            if (item.containsKey("GridView") && item.containsKey("tListDescription")) {
                viewHolder.catDescTxt.setVisibility(View.VISIBLE);
                viewHolder.catDescTxt.setText(item.get("tListDescription"));

                new android.os.Handler(Looper.getMainLooper()).postDelayed(() -> {
                    if (viewHolder.catNameTxt.getLineCount() == 1) {
                        viewHolder.catNameTxt.setMinLines(1);
                        viewHolder.catDescTxt.setMinLines(4);
                    }
                }, 20);
            }

            String imageURL;
            if (isDaynamic) {
                imageURL = Utils.getResizeImgURL(mContext, vListLogo != null ? vListLogo : "", daynamic_width, daynamic_height);
            } else {
                imageURL = Utils.getResizeImgURL(mContext, vListLogo != null ? vListLogo : "", grid, grid);
            }

            new LoadImage.builder(LoadImage.bind(imageURL), viewHolder.catImgView).setErrorImagePath(R.mipmap.ic_no_icon).setPlaceholderImagePath(R.mipmap.ic_no_icon).build();

            viewHolder.contentArea.setOnClickListener(view -> {
                //Boolean isMore = false;
                if (onItemClickList != null) {
                    if (eShowTerms && !CommonUtilities.ageRestrictServices.contains("1")) {
                        new showTermsDialog(mContext, generalFunc, position, vCategory, false, () -> onItemClickList.onItemClick(position, parentPos,/*, isMore*/item));
                    } else {
                        onItemClickList.onItemClick(position, parentPos,/*, isMore*/item);
                    }
                }
            });
        } else if (parentViewHolder instanceof HeaderViewHolder) {
            HashMap<String, String> item = list_item.get(position);
            HeaderViewHolder headerHolder = (HeaderViewHolder) parentViewHolder;
            headerHolder.eTypeTxt.setText(item.get("vTitle"));
            try {
                ArrayList<HashMap<String, String>> list_item1 = new ArrayList<>();
                JSONArray serviceArr = new JSONArray(item.get("serviceArr"));
                for (int kk = 0; kk < serviceArr.length(); kk++) {
                    HashMap<String, String> innerMap = new HashMap<>();
                    JSONObject serviceObj = generalFunc.getJsonObject(serviceArr, kk);
                    innerMap.put("iVehicleCategoryId", generalFunc.getJsonValueStr("iVehicleCategoryId", serviceObj));
                    String eShowTerms = generalFunc.getJsonValueStr("eShowTerms", serviceObj);
                    innerMap.put("eShowTerms", Utils.checkText(eShowTerms) ? eShowTerms : "");
                    innerMap.put("vCategory", generalFunc.getJsonValueStr("vCategory", serviceObj));
                    if (item.get("GridView").equalsIgnoreCase("Yes")) {
                        innerMap.put("tListDescription", generalFunc.getJsonValueStr("tListDescription", serviceObj));
                        innerMap.put("GridView", "Yes");
                    }
                    innerMap.put("vListLogo", generalFunc.getJsonValueStr("vListLogo", serviceObj));
                    innerMap.put("eCatType", generalFunc.getJsonValueStr("eCatType", serviceObj));
                    innerMap.put("iParentId", generalFunc.getJsonValueStr("iParentId", serviceObj));
                    innerMap.put("iServiceId", generalFunc.getJsonValueStr("iServiceId", serviceObj));
                    innerMap.put("eForMedicalService", list_item.get(position).get("Type").equalsIgnoreCase("eForMedicalService") ? "Yes" : "No");
                    innerMap.put("eVideoConsultEnable", generalFunc.getJsonValueStr("eVideoConsultEnable", serviceObj));
                    list_item1.add(innerMap);
                }
                if (item.get("GridView").equalsIgnoreCase("Yes")) {
                    headerHolder.dataListRecyclerView_more.setLayoutManager(new GridLayoutManager(mContext, 2));
                } else {
                    headerHolder.dataListRecyclerView_more.setLayoutManager(new GridLayoutManager(mContext, getNumOfColumns() < 4 ? 4 : getNumOfColumns()));
                }
                UberXCategoryMoreAdapter ufxCatAdapter = new UberXCategoryMoreAdapter(mContext, list_item1, position, generalFunc);
                headerHolder.dataListRecyclerView_more.setAdapter(ufxCatAdapter);
                ufxCatAdapter.setOnItemClickList(new UberXCategoryMoreAdapter.OnItemClickList() {
                    @Override
                    public void onItemClick(int mPosition, int parentPos, HashMap<String, String> mList) {
                        onItemClickList.onItemClick(mPosition, position, list_item1.get(mPosition));
                    }

                    @Override
                    public void onMultiItem(String id, boolean b) {

                    }
                });
            } catch (JSONException e) {
                Logger.e("Exception","::"+e.getMessage());
            }
        }
    }

    public void setListUpdate(ArrayList<HashMap<String, String>> list_item) {
        this.list_item = list_item;
    }

    @Override
    public int getItemViewType(int position) {
        if (list_item.get(position).containsKey("Type") && list_item.get(position).get("Type").equalsIgnoreCase("eForMedicalService")) {
            return TYPE_HEADER;
        }
        return TYPE_LIST;
    }

    private Integer getNumOfColumns() {
        try {
            //float margin_int_value = mContext.getResources().getDimensionPixelSize(R.dimen._20sdp);
            float margin_int_value = Utils.dipToPixels(mContext, 20);
            float catWidth_int_value = Utils.dipToPixels(mContext, 100);
            float screenWidth_int_value = Utils.getScreenPixelWidth(mContext) - margin_int_value;
            return (int) (screenWidth_int_value / catWidth_int_value);
        } catch (Exception e) {
            Logger.e("Exception","::"+e.getMessage());
        }
        return -1;
    }

    private class SetOnTouchList implements View.OnTouchListener {

        int viewType;
        int item_position;
        //boolean isBlockDownEvent = false;

        public SetOnTouchList(int viewType, int item_position/*, boolean isBlockDownEvent*/) {
            this.item_position = item_position;
            this.viewType = viewType;
            //this.isBlockDownEvent = isBlockDownEvent;
        }

        @SuppressLint("ClickableViewAccessibility")
        @Override
        public boolean onTouch(View view, MotionEvent motionEvent) {

            switch (motionEvent.getAction()) {
                case MotionEvent.ACTION_DOWN:

                    break;
                case MotionEvent.ACTION_UP:
                    if (list_item.size() > item_position && (list_item.get(item_position).get("LAST_CLICK_TIME") == null || (System.currentTimeMillis() - GeneralFunctions.parseLongValue(0, list_item.get(item_position).get("LAST_CLICK_TIME"))) > 1000)) {
                        scaleView(view, motionEvent.getAction(), viewType, item_position);
                    } else {
                        scaleView(view, MotionEvent.ACTION_CANCEL, viewType, item_position);
                    }
                    break;
                case MotionEvent.ACTION_CANCEL:
                    scaleView(view, motionEvent.getAction(), viewType, item_position);
                    break;
            }
            return true;
        }
    }

    private void scaleView(View v, int motionEvent, int viewType, int item_position) {
        v.setOnTouchListener(new SetOnTouchList(viewType, item_position/*, true*/));
        Animation anim = new ScaleAnimation(
                (float) 1.0, (float) 1.0, // Start and end values for the X axis scaling
                (float) 1.0, (float) 1.0, // Start and end values for the Y axis scaling
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
                v.setOnTouchListener(new SetOnTouchList(viewType, item_position/*, false*/));
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

    public void setOnItemClickList(OnItemClickList onItemClickList) {
        this.onItemClickList = onItemClickList;
    }

    public interface OnItemClickList {
        void onItemClick(int position, int parentPos,/*, Boolean isMore*/HashMap<String, String> mList);

        void onMultiItem(String id, boolean b);
    }

    private static class HeaderViewHolder extends RecyclerView.ViewHolder {

        private final MTextView eTypeTxt;
        private final RecyclerView dataListRecyclerView_more;

        public HeaderViewHolder(View view) {
            super(view);
            eTypeTxt = (MTextView) view.findViewById(R.id.eTypeTxt);
            dataListRecyclerView_more = (RecyclerView) view.findViewById(R.id.dataListRecyclerView_more);
        }
    }

    private static class ListViewHolderHorizontalMore extends RecyclerView.ViewHolder {

        private final TextView catNameTxt, catDescTxt;
        private final View contentArea;
        private final ImageView catImgView;

        public ListViewHolderHorizontalMore(View view) {
            super(view);
            catNameTxt = (TextView) view.findViewById(R.id.catNameTxt);
            catDescTxt = (TextView) view.findViewById(R.id.catDescTxt);
            contentArea = view.findViewById(R.id.contentArea);
            catImgView = (ImageView) view.findViewById(R.id.catImgView);
            catDescTxt.setVisibility(View.GONE);
        }
    }
}