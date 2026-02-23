package com.adapter.files;

import android.annotation.SuppressLint;
import android.content.Context;
import android.graphics.Color;
import android.text.Html;
import android.text.Spanned;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageView;
import android.widget.LinearLayout;

import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;

import com.fontanalyzer.SystemFont;
import com.general.files.GeneralFunctions;
import com.buddyverse.main.R;
import com.utils.Utils;
import com.view.AutoResizeTextView;
import com.view.MTextView;
import com.view.anim.loader.AVLoadingIndicatorView;

import java.util.ArrayList;
import java.util.HashMap;

public class CustomDialogListAdapter extends RecyclerView.Adapter<CustomDialogListAdapter.ViewHolder> {

    Context mContext;
    private ArrayList<HashMap<String, String>> listItem;
    OnItemClickList onItemClickList;
    boolean arrowForwardImageShow;
    int selectedItemPosition;
    String keyToShowAsTitle;

    int selectedColor;
    int nonSelectedColor;

    boolean onlyStringArrayList;
    private ArrayList<String> stringListItem;
    boolean isImageInList;
    int selpos = -1;
    private final GeneralFunctions generalFunctions;

    public CustomDialogListAdapter(Context mContext, ArrayList<HashMap<String, String>> listItem, int selectedItemPosition, String keyToShowAsTitle, boolean isImageInList, GeneralFunctions generalFunctions) {
        this.mContext = mContext;
        this.listItem = listItem;
        this.selectedItemPosition = selectedItemPosition;
        this.keyToShowAsTitle = keyToShowAsTitle;

        selectedColor = mContext.getResources().getColor(R.color.appThemeColor_1);
        nonSelectedColor = Color.parseColor("#646464");
        this.isImageInList = isImageInList;
        this.generalFunctions = generalFunctions;
    }

    public CustomDialogListAdapter(Context mContext, ArrayList<String> stringListItem, int selectedItemPosition, boolean onlyStringArrayList, boolean isImageInList, GeneralFunctions generalFunctions) {
        this.mContext = mContext;
        this.stringListItem = stringListItem;
        this.selectedItemPosition = selectedItemPosition;
        this.onlyStringArrayList = onlyStringArrayList;

        selectedColor = mContext.getResources().getColor(R.color.appThemeColor_1);
        nonSelectedColor = Color.parseColor("#646464");
        this.isImageInList = isImageInList;
        this.generalFunctions = generalFunctions;
    }

    @NonNull
    @Override
    public ViewHolder onCreateViewHolder(@NonNull ViewGroup viewGroup, int i) {
        View view = LayoutInflater.from(viewGroup.getContext()).inflate(R.layout.item_list_design, viewGroup, false);
        if (isImageInList) {
            view = LayoutInflater.from(viewGroup.getContext()).inflate(R.layout.item_image_list_design, viewGroup, false);
        }

        return new ViewHolder(view);
    }

    @Override
    public int getItemViewType(int position) {
        return super.getItemViewType(position);
    }

    @Override
    public void onBindViewHolder(@NonNull ViewHolder viewHolder, @SuppressLint("RecyclerView") int i) {


        String str = onlyStringArrayList ? stringListItem.get(i) : listItem.get(i).get(keyToShowAsTitle);
        Spanned strHFrom = Html.fromHtml(str);
        if (strHFrom.length() > 0) {
            viewHolder.rowTitleTxtView.setText(strHFrom);
        } else {
            viewHolder.rowTitleTxtView.setText(str);
        }

        if (isImageInList) {
            if (Utils.checkText(listItem.get(i).get("vCode"))) {
                viewHolder.codeTxt.setMinTextSize(mContext.getResources().getDimensionPixelSize(R.dimen._6ssp));
                viewHolder.codeTxt.setText(listItem.get(i).get("vCode"));
            }
            if (Utils.checkText(listItem.get(i).get("vService_BG_color"))) {
                viewHolder.codeArea.getBackground().setTint(Color.parseColor(listItem.get(i).get("vService_BG_color")));
            }
        }

        if (selectedItemPosition == i) {
            if (isImageInList) {
                viewHolder.imgCheck.setImageResource(R.drawable.ic_select_check_mark);
            } else {
                viewHolder.rowTitleTxtView.setTextColor(selectedColor);
                viewHolder.rowTitleTxtView.setTypeface(SystemFont.FontStyle.BOLD.font);
                if (arrowForwardImageShow) {
                    viewHolder.imgCheck.setImageResource(R.drawable.arrow_forward);
                }
                viewHolder.imgCheck.setColorFilter(selectedColor, android.graphics.PorterDuff.Mode.SRC_IN);
            }
        } else {
            if (isImageInList) {
                viewHolder.imgCheck.setImageResource(R.drawable.ic_un_select_check_mark);
            } else {
                viewHolder.rowTitleTxtView.setTypeface(SystemFont.FontStyle.REGULAR.font);
                viewHolder.rowTitleTxtView.setTextColor(nonSelectedColor);
                if (arrowForwardImageShow) {
                    viewHolder.imgCheck.setImageResource(R.drawable.arrow_forward);
                }
                viewHolder.imgCheck.setColorFilter(nonSelectedColor, android.graphics.PorterDuff.Mode.SRC_IN);
            }
        }
        if (isImageInList) {
            if (selpos != -1 && selpos == i) {
                viewHolder.progress.setVisibility(View.VISIBLE);
                viewHolder.imgCheck.setVisibility(View.GONE);
            } else {
                viewHolder.progress.setVisibility(View.GONE);

                if (isprogressEnable) {
                    viewHolder.imgCheck.setVisibility(View.GONE);
                } else {
                    viewHolder.imgCheck.setVisibility(View.VISIBLE);
                }
            }
        }
        viewHolder.mainArea.setOnClickListener(v -> {
            if (onItemClickList != null) {
                if (i != selectedItemPosition) {
                    onItemClickList.onItemClick(i);
                    if (isImageInList) {

                        if (selpos == -1 || selpos == selectedItemPosition) {
                            selpos = i;
                            viewHolder.progress.setVisibility(View.VISIBLE);
                            viewHolder.imgCheck.setVisibility(View.GONE);
                            isprogressEnable = true;
                            notifyDataSetChanged();
                        }
                    }
                }
            }
        });
    }

    boolean isprogressEnable = false;

    @Override
    public int getItemCount() {
        return onlyStringArrayList ? stringListItem.size() : listItem.size();
    }

    public class ViewHolder extends RecyclerView.ViewHolder {
        MTextView rowTitleTxtView;
        ImageView imgCheck;
        LinearLayout mainArea;
        AutoResizeTextView codeTxt;
        LinearLayout codeArea;
        AVLoadingIndicatorView progress;

        public ViewHolder(@NonNull View itemView) {
            super(itemView);
            rowTitleTxtView = itemView.findViewById(R.id.rowTitleTxtView);
            imgCheck = itemView.findViewById(R.id.imgCheck);
            mainArea = itemView.findViewById(R.id.mainArea);
            if (generalFunctions.isRTLmode()) {
                imgCheck.setRotationY(180);
            }

            if (isImageInList) {
                codeTxt = itemView.findViewById(R.id.codeTxt);
                codeArea = itemView.findViewById(R.id.codeArea);
                progress = itemView.findViewById(R.id.progress);
            }
        }
    }

    public void setOnItemClickList(OnItemClickList onItemClickList) {
        this.onItemClickList = onItemClickList;
    }

    public void setArrowImage(boolean value) {
        this.arrowForwardImageShow = value;
    }

    public interface OnItemClickList {
        void onItemClick(int position);
    }
}