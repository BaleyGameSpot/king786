package com.adapter.files;

import android.annotation.SuppressLint;
import android.text.Editable;
import android.text.InputType;
import android.text.TextWatcher;
import android.view.LayoutInflater;
import android.view.MotionEvent;
import android.view.View;
import android.view.ViewGroup;
import android.view.inputmethod.EditorInfo;
import android.widget.ImageView;
import android.widget.LinearLayout;

import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;

import com.act.SearchLocationActivity;
import com.general.files.GeneralFunctions;
import com.buddyverse.main.R;
import com.model.Stop_Over_Points_Data;
import com.utils.MyUtils;
import com.utils.Utils;
import com.view.DividerView;
import com.view.editBox.MaterialEditText;

import java.util.ArrayList;
import java.util.Arrays;

/**
 * Created by Admin on 09-07-2016.
 */
public class StopOverPointsAdapter extends RecyclerView.Adapter<RecyclerView.ViewHolder> {

    public GeneralFunctions generalFunc;
    public int pos = -1; // initial pos value
    ArrayList<Stop_Over_Points_Data> list;
    ArrayList<ViewHolder> viewHolderList = new ArrayList<>();
    SearchLocationActivity mContext;
    boolean addTextChangeListner = false;
    boolean selecetdViewList[];
    public boolean isTouch = false; // is in edit mode
    boolean isFirstTime = false;
    String LBL_PICK_UP_FROM, LBL_STOP_OVER_TXT, LBL_DROP_AT, LBL_WHERE_TO;
    private onStopOverClickListener onStopOverClickListener;

    RecyclerView stopOverPointsSubRecyclerView;
    public boolean isAddressAdded = false;

    public StopOverPointsAdapter(SearchLocationActivity mContext, ArrayList<Stop_Over_Points_Data> list, RecyclerView stopOverPointsSubRecyclerView) {
        this.mContext = mContext;
        this.list = list;
        this.generalFunc = mContext.generalFunc;
        this.selecetdViewList = new boolean[list.size()];
        Arrays.fill(selecetdViewList, false);
        isFirstTime = true;
        LBL_PICK_UP_FROM = generalFunc.retrieveLangLBl("", "LBL_PICK_UP_FROM");
        LBL_STOP_OVER_TXT = generalFunc.retrieveLangLBl("", "LBL_STOP_OVER_TXT");
        LBL_DROP_AT = generalFunc.retrieveLangLBl("", "LBL_WHERE_TO");
        LBL_WHERE_TO = generalFunc.retrieveLangLBl("Where To?", "LBL_WHERE_TO");
        this.stopOverPointsSubRecyclerView = stopOverPointsSubRecyclerView;
    }

    public StopOverPointsAdapter(SearchLocationActivity mContext, ArrayList<Stop_Over_Points_Data> list, boolean addTextChangeListner, RecyclerView stopOverPointsSubRecyclerView) {
        this.mContext = mContext;
        this.list = list;
        this.generalFunc = mContext.generalFunc;
        this.addTextChangeListner = addTextChangeListner;
        this.selecetdViewList = new boolean[list.size()];
        Arrays.fill(selecetdViewList, false);
        isFirstTime = true;
        LBL_PICK_UP_FROM = generalFunc.retrieveLangLBl("", "LBL_PICK_UP_FROM");
        LBL_STOP_OVER_TXT = generalFunc.retrieveLangLBl("", "LBL_STOP_OVER_TXT");
        LBL_DROP_AT = generalFunc.retrieveLangLBl("", "LBL_STOP_OVER_TXT");
        LBL_WHERE_TO = generalFunc.retrieveLangLBl("Where To?", "LBL_WHERE_TO");
        this.stopOverPointsSubRecyclerView = stopOverPointsSubRecyclerView;
    }


    public void setOnItemClickListener(onStopOverClickListener onStopOverClickList) {
        this.onStopOverClickListener = onStopOverClickList;
    }

    @NonNull
    @Override
    public RecyclerView.ViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        return new ViewHolder(LayoutInflater.from(mContext).inflate(R.layout.design_stopover_locations, parent, false));
    }

    // Replace the contents of a view (invoked by the layout manager)
    @Override
    public void onBindViewHolder(@NonNull final RecyclerView.ViewHolder holder, @SuppressLint("RecyclerView") final int position) {

        final Stop_Over_Points_Data item = list.get(position);
        final ViewHolder viewHolder = (ViewHolder) holder;

        viewHolderList.add(viewHolder);

        int listSize = list.size() - 1;

        pos = addTextChangeListner ? mContext.subSelectedPos : mContext.selectedPos;
        this.selecetdViewList = new boolean[list.size()];
        Arrays.fill(selecetdViewList, false);

        if (pos != -1) {
            selecetdViewList[pos] = true;

        }

        //boolean isDestinationAdded = Utils.checkText(list.get(position).getDestAddress());

        item.setHintLable(position == 0 ? LBL_PICK_UP_FROM : (position == listSize ? LBL_DROP_AT : LBL_STOP_OVER_TXT));
        if (position == 1 && listSize == 1) {
            item.setHintLable(LBL_WHERE_TO);
        }


        if (position == pos && selecetdViewList[pos]) {

            //  viewHolder.stopOverPoint.setBackgroundColor(mContext.getResources().getColor(R.color.mspSelectedColor));
            // viewHolder.stopOverLocationArea.setBackground(mContext.getResources().getDrawable(R.drawable.material_card));
            viewHolder.stopOverPoint.setTextColor(mContext.getResources().getColor(R.color.mspSelected));
            //  isTouch = true;
            viewHolder.stopOverPoint.setCursorVisible(true);
            viewHolder.stopOverPoint.setInputType(InputType.TYPE_CLASS_TEXT);
            viewHolder.stopOverPoint.setFocusableInTouchMode(true);
            viewHolder.stopOverPoint.setFocusable(true);
            if (addTextChangeListner) {
                mContext.materialEditText = viewHolder.stopOverPoint;
                if (!mContext.isSetOkResult) {
                    Utils.showSoftKeyboard(mContext, viewHolder.stopOverPoint);
                    mContext.searchSourceOrDest(viewHolder.stopOverPoint.getText().toString(), position);
                }
            }


        } else {

            //  viewHolder.stopOverPoint.setBackgroundColor(mContext.getResources().getColor(R.color.mspUnSelectedColor));
            // viewHolder.stopOverLocationArea.setBackground(null);
            viewHolder.stopOverPoint.setTextColor(mContext.getResources().getColor(R.color.mspUnSelected));
            viewHolder.stopOverPoint.setCursorVisible(false);
            viewHolder.stopOverPoint.setInputType(0);
            viewHolder.stopOverPoint.setFocusableInTouchMode(false);
            viewHolder.stopOverPoint.setFocusable(false);
        }

        viewHolder.stopOverPoint.setTag(position);
        viewHolder.stopOverLocationArea.setTag(position);
        viewHolder.iv_removeStopPoint.setTag(position);
        viewHolder.removeArea.setTag(position);
        viewHolder.cancelArea.setTag(position);
        viewHolder.imageCancel.setTag(position);
        viewHolder.iv_addStopPoint.setTag(position);

        //  DrawableCompat.setTint(viewHolder.iv_round_img.getDrawable(), ContextCompat.getColor(mContext, R.color.highlightedColor));
        //  viewHolder.squareImgView.setBackgroundColor(mContext.getResources().getColor(R.color.highlightedColor));

        viewHolder.squareImgView.setVisibility(position == 0 || position == listSize ? View.GONE : View.VISIBLE);
        viewHolder.iv_loc_img.setVisibility(position == 0 ? View.VISIBLE : View.GONE);
        viewHolder.iv_round_img.setVisibility(position == listSize ? View.VISIBLE : View.GONE);
        viewHolder.aboveLine.setVisibility(position == 0 ? View.INVISIBLE : View.VISIBLE);
        viewHolder.lowerLine.setVisibility(position == listSize ? View.INVISIBLE : View.VISIBLE);

        boolean isStopOver = generalFunc.parseIntegerValue(4, generalFunc.retrieveValue(Utils.MAX_NUMBER_STOP_OVER_POINTS_KEY)) < 1;
        viewHolder.iv_addStopPoint.setVisibility((mContext.isRental.equalsIgnoreCase("true") ||
                mContext.iscubejekRental.equalsIgnoreCase("true") ||
                isStopOver || mContext.isSchedule || mContext.isTaxiBid) ? View.GONE : (addTextChangeListner && position != 0 ? View.VISIBLE : View.GONE));

        if (!addTextChangeListner) {
            viewHolder.removeArea.setVisibility(position == 0 ? View.INVISIBLE : (position == listSize ? View.INVISIBLE : (Utils.checkText(item.getDestAddress()) ? View.VISIBLE : (position == 1 ? View.VISIBLE : View.INVISIBLE))));
            viewHolder.stopOverPoint.setCursorVisible(false);
            viewHolder.stopOverPoint.setSelection(0);
            Utils.removeInput(viewHolder.stopOverPoint);
        } else {
            viewHolder.removeArea.setVisibility(View.GONE);

            stopOverPointsSubRecyclerView.postDelayed(() -> stopOverPointsSubRecyclerView.scrollToPosition(list.size()), 1000);
        }


        viewHolder.iv_addStopPoint.setOnClickListener(v -> MyUtils.setBounceAnimation(mContext, viewHolder.iv_addStopPoint, R.anim.bounce_interpolator, () -> {
            if (onStopOverClickListener != null) {
                onStopOverClickListener.onStopOverClickList(viewHolder.stopOverPoint, "AddBlankAddress", position);
            }
        }));


        viewHolder.removeArea.setOnClickListener(v -> MyUtils.setBounceAnimation(mContext, viewHolder.removeArea, R.anim.bounce_interpolator, () -> {
            if (onStopOverClickListener != null) {
                onStopOverClickListener.onStopOverClickList(viewHolder.stopOverPoint, "Remove", position);
            }

        }));


        if (addTextChangeListner) {

            viewHolder.cancelArea.setOnTouchListener((v, event) -> {
                MyUtils.setBounceAnimation(mContext, viewHolder.cancelArea, R.anim.bounce_interpolator, () -> {
                    mContext.subSelectedPos = position;
                    viewHolder.stopOverPoint.setText("");
                    v.setVisibility(View.INVISIBLE);
                    viewHolder.imageCancel.setVisibility(View.INVISIBLE);
                    isTouch = true;
                });
                return true;
            });


            viewHolder.stopOverPoint.addTextChangedListener(new TextWatcher() {
                @Override
                public void beforeTextChanged(CharSequence s, int start, int count, int after) {

                }

                @Override
                public void onTextChanged(CharSequence s, int start, int before, int count) {

                }

                @Override
                public void afterTextChanged(Editable s) {
                    if (mContext.isFinishing()) {
                        return;
                    }

                    if (s != null) {

                        String searchAddress = s.toString().trim();
                        String destTempAddress = list.get(position).getDestTempAddress();
                        boolean isTempLoc = list.get(position).isTempLoc();

                        if (isTouch && pos == position) {
                            if (isTempLoc && Utils.checkText(destTempAddress) && !(destTempAddress.equalsIgnoreCase(s.toString()))) {
                                mContext.searchSourceOrDest(viewHolder.stopOverPoint.getText().toString(), position);
                            } else if (searchAddress != null && !searchAddress.equalsIgnoreCase("")) {
                                mContext.searchSourceOrDest(searchAddress, position);
                            } else if (!(destTempAddress.equalsIgnoreCase(s.toString()))) {
                                mContext.searchSourceOrDest(destTempAddress, position);
                            }
                            list.get(position).setDestTempAddress(s.toString());

                        }
                    }


                }
            });

            viewHolder.stopOverPoint.setOnEditorActionListener((v, actionId, event) -> {
                mContext.subSelectedPos = position;

                if (actionId == EditorInfo.IME_ACTION_SEARCH) {
                    mContext.getSearchGooglePlace(v.getText().toString(), position);
                    return true;
                }
                return false;
            });

        }

        viewHolder.stopOverPoint.setOnTouchListener((v, event) -> {

            if (event.getAction() == MotionEvent.ACTION_UP) {
                if (addTextChangeListner) {
                    mContext.selectedPos = position;
                } else {
                    mContext.subSelectedPos = position;
                }

                isTouch = true;
                // showCancelButton(viewHolder);
                MyUtils.setBounceAnimation(mContext, viewHolder.stopOverPoint, R.anim.bounce_interpolator, () -> {

                    if (onStopOverClickListener != null) {
                        onStopOverClickListener.onStopOverClickList(viewHolder.stopOverPoint, "", position);
                    }
                });
            }

            return false;
        });


        boolean addTempLocation = addTextChangeListner && item.isTempLoc() && Utils.checkText(item.getDestTempAddress());


        String hintText = item.getHintLable();

        viewHolder.stopOverPoint.setHint(hintText /*addTempLocation ? (Utils.checkText(item.getDestTempAddress()) ? hintText : item.getDestTempAddress()) : (Utils.checkText(item.getDestAddress()) ? item.getDestAddress() : hintText)*/);

        String setText = addTempLocation ? item.getDestTempAddress() : item.getDestAddress();
        viewHolder.stopOverPoint.setText(setText);

        if (addTextChangeListner) {
            viewHolder.stopOverPoint.setSelection(position == pos && selecetdViewList[pos] ? viewHolder.stopOverPoint.getText().length() : 0);
            viewHolder.cancelArea.setVisibility(position == pos && selecetdViewList[pos] && Utils.checkText(viewHolder.stopOverPoint) ? View.VISIBLE : View.GONE);
            viewHolder.imageCancel.setVisibility(position == pos && selecetdViewList[pos] && Utils.checkText(viewHolder.stopOverPoint) ? View.VISIBLE : View.GONE);
        } else {
            viewHolder.cancelArea.setVisibility(View.GONE);
            viewHolder.imageCancel.setVisibility(View.GONE);
        }

        showCancelButton(viewHolder);


        if (isAddressAdded) {
            isAddressAdded = false;
            stopOverPointsSubRecyclerView.postDelayed(() -> stopOverPointsSubRecyclerView.smoothScrollToPosition(list.size()), 1000);
        }


    }

    private void showCancelButton(ViewHolder viewHolder) {
        boolean isRtlMode = generalFunc.isRTLmode();

        viewHolder.btnArea.setVisibility(View.VISIBLE);
        if (viewHolder.cancelArea.getVisibility() == View.VISIBLE) {
//            int padding = (int) mContext.getResources().getDimension(R.dimen._15sdp);
            int padding = 0;
            int padding1 = (int) mContext.getResources().getDimension(R.dimen._10sdp);

            int left = isRtlMode ? padding : padding1;
            int right = !isRtlMode ? padding : padding1;


            viewHolder.stopOverPoint.setPadding(left, padding1, right, padding1);

        } else if (viewHolder.removeArea.getVisibility() == View.VISIBLE) {
            //            int padding = (int) mContext.getResources().getDimension(R.dimen._30sdp);
            int padding = 0;
            int padding1 = viewHolder.stopOverLocationArea.getBackground() != null ? (int) mContext.getResources().getDimension(R.dimen._10sdp) : (int) mContext.getResources().getDimension(R.dimen._5sdp);

            int left = isRtlMode ? padding : padding1;
//            int right=!isRtlMode ? padding : (isRtlMode ? padding1 : 0);
            int right = 0;

            viewHolder.stopOverPoint.setPadding(left, padding1, right, padding1);

        } else if (viewHolder.iv_addStopPoint.getVisibility() == View.VISIBLE) {
//            int padding = (int) mContext.getResources().getDimension(R.dimen._30sdp);
            int padding = 0;
            int padding1 = viewHolder.stopOverLocationArea.getBackground() != null ? (int) mContext.getResources().getDimension(R.dimen._10sdp) : (int) mContext.getResources().getDimension(R.dimen._5sdp);

            int left = isRtlMode ? padding : padding1;
            int right = !isRtlMode ? padding : padding1;


            viewHolder.stopOverPoint.setPadding(left, padding1, right, padding1);

        } else {
            int padding1 = viewHolder.stopOverLocationArea.getBackground() != null ? (int) mContext.getResources().getDimension(R.dimen._10sdp) : 0;
//            int left =!isRtlMode ? padding1 : 0;
//            int right =!isRtlMode ? 0 : padding1;
            viewHolder.btnArea.setVisibility(View.GONE);
            viewHolder.stopOverPoint.setPadding(padding1, 0, padding1, 0);
        }

    }

    @Override
    public long getItemId(int position) {
        return super.getItemId(position);
    }

    @Override
    public int getItemViewType(int position) {
        return position;
    }


    // Return the size of your itemsData (invoked by the layout manager)
    @Override
    public int getItemCount() {
        return list.size();

    }


    public interface onStopOverClickListener {
        void onStopOverClickList(MaterialEditText materialEditText, String type, int position);
    }

    public void setClick(int position) {
        if (addTextChangeListner) {
            mContext.selectedPos = position;
        } else {
            mContext.subSelectedPos = position;
        }
        isTouch = true;
        if (onStopOverClickListener != null) {
            onStopOverClickListener.onStopOverClickList(viewHolderList.get(position).stopOverPoint, "", position);
        }
    }

    // inner class to hold a reference to each item of RecyclerView
    private class ViewHolder extends RecyclerView.ViewHolder {

        public MaterialEditText stopOverPoint;
        private ImageView iv_round_img, iv_loc_img, imageCancel, iv_addStopPoint, iv_removeStopPoint;
        private DividerView lowerLine, aboveLine;
        private View squareImgView;
        private LinearLayout cancelArea;
        private LinearLayout stopOverLocationArea;
        private LinearLayout btnArea;
        private LinearLayout removeArea;


        private ViewHolder(View view) {
            super(view);

            iv_addStopPoint = (ImageView) view.findViewById(R.id.iv_addStopPoint);
            iv_removeStopPoint = (ImageView) view.findViewById(R.id.iv_removeStopPoint);
            stopOverLocationArea = (LinearLayout) view.findViewById(R.id.stopOverLocationArea);
            btnArea = (LinearLayout) view.findViewById(R.id.btnArea);
            iv_round_img = (ImageView) view.findViewById(R.id.iv_round_img);
            iv_loc_img = (ImageView) view.findViewById(R.id.iv_loc_img);
            imageCancel = (ImageView) view.findViewById(R.id.imageCancel);
            aboveLine = (DividerView) view.findViewById(R.id.aboveLine);
            lowerLine = (DividerView) view.findViewById(R.id.lowerLine);
            squareImgView = (View) view.findViewById(R.id.squareImgView);
            stopOverPoint = (MaterialEditText) view.findViewById(R.id.stopOverPoint);
            cancelArea = (LinearLayout) view.findViewById(R.id.cancelArea);
            removeArea = (LinearLayout) view.findViewById(R.id.removeArea);
        }
    }
}