package com.adapter.files;

import android.annotation.SuppressLint;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.LinearLayout;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.core.content.ContextCompat;
import androidx.recyclerview.widget.RecyclerView;

import com.general.files.ActUtils;
import com.general.files.GeneralFunctions;
import com.buddyverse.providers.R;
import com.buddyverse.providers.databinding.ListOfDocItemDesignBinding;
import com.view.MButton;
import com.view.MaterialRippleLayout;

import java.util.ArrayList;
import java.util.HashMap;

public class ListOfDocAdapter extends RecyclerView.Adapter<RecyclerView.ViewHolder> {

    private static final int TYPE_ITEM = 1, TYPE_FOOTER = 2;
    private final GeneralFunctions generalFunc;
    private final OnItemClickListener mItemClickListener;
    private final ArrayList<HashMap<String, String>> list;
    private boolean isFooterEnabled = false;
    private FooterViewHolder footerHolder;
    private final int currSelectedPosition = -1;

    public ListOfDocAdapter(@NonNull GeneralFunctions generalFunc, @NonNull ArrayList<HashMap<String, String>> list, @Nullable OnItemClickListener mItemClickListener) {
        this.generalFunc = generalFunc;
        this.list = list;
        this.mItemClickListener = mItemClickListener;
    }

    @NonNull
    @Override
    public RecyclerView.ViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        if (viewType == TYPE_FOOTER) {
            return new FooterViewHolder(LayoutInflater.from(parent.getContext()).inflate(R.layout.footer_list, parent, false));
        } else {
            return new ViewHolder(ListOfDocItemDesignBinding.inflate(LayoutInflater.from(parent.getContext()), parent, false));
        }
    }

    // Replace the contents of a view (invoked by the layout manager)
    @Override
    public void onBindViewHolder(@NonNull final RecyclerView.ViewHolder holder, final int position) {

        if (holder instanceof final ViewHolder viewHolder) {

            final HashMap<String, String> item = list.get(position);

            viewHolder.binding.titleTxt.setText(item.get("doc_name"));

            //new CreateRoundedView(Color.parseColor("#ffffff"), (int) mContext.getResources().getDimension(R.dimen._6sdp), 2, mContext.getResources().getColor(R.color.appThemeColor_1), viewHolder.main_layout);
            //CHANGES FOR DOCUMENT MISSING


            if (item.get("doc_file").equalsIgnoreCase("")) {
                viewHolder.binding.missingTxt.setText(generalFunc.retrieveLangLBl("Upload your document", "LBL_UPLOAD_YOUR_DOCS"));
                viewHolder.binding.infoImg.setImageDrawable(ContextCompat.getDrawable(viewHolder.itemView.getContext(), R.drawable.ic_warning));

            } else {

                if (item.get("EXPIRE_DOCUMENT").equalsIgnoreCase("Yes")) {
                    viewHolder.binding.missingTxt.setText(item.get("LBL_EXPIRED_TXT"));
                    viewHolder.binding.infoImg.setImageDrawable(ContextCompat.getDrawable(viewHolder.itemView.getContext(), R.drawable.ic_warning));
                } else {
                    viewHolder.binding.missingTxt.setText(item.get("exp_date"));
                    viewHolder.binding.infoImg.setImageDrawable(ContextCompat.getDrawable(viewHolder.itemView.getContext(), R.mipmap.ic_right));
                    viewHolder.binding.infoImg.setColorFilter(ContextCompat.getColor(viewHolder.itemView.getContext(), R.color.appThemeColor_1), android.graphics.PorterDuff.Mode.SRC_IN);
                }

                if (item.get("ex_status").equalsIgnoreCase("yes")) {
                    viewHolder.binding.missingTxt.setVisibility(View.VISIBLE);
                } else {
                    viewHolder.binding.missingTxt.setVisibility(View.GONE);
                }
            }


            //CHANGES OVER FOR DOCUMENT MISSING
            MButton manageBtn = ((MaterialRippleLayout) viewHolder.binding.manageBtn).getChildView();

            String vImage = item.get("vimage");
            if (vImage.equals("")) {
                viewHolder.binding.docImgView.setImageResource(R.drawable.ic_doc_off);
                viewHolder.binding.docImgView.setOnClickListener(null);
                manageBtn.setText(item.get("LBL_UPLOAD_DOC"));
                viewHolder.binding.docImgView.setVisibility(View.GONE);
            } else {
                viewHolder.binding.docImgView.setVisibility(View.VISIBLE);
                manageBtn.setText(item.get("LBL_MANAGE"));

                viewHolder.binding.docImgView.setOnClickListener(view -> new ActUtils(viewHolder.itemView.getContext()).openURL(vImage));
                viewHolder.binding.docImgView.setImageResource(R.drawable.ic_doc_on);
            }

            if (currSelectedPosition == -1 || currSelectedPosition != position) {
                // viewHolder.indicatorImg.setImageResource(R.mipmap.ic_arrow_right);
                viewHolder.binding.detailArea.setVisibility(View.GONE);
                viewHolder.binding.seperatorView.setVisibility(View.GONE);
            } else {
                // viewHolder.indicatorImg.setImageResource(R.mipmap.ic_arrow_right);
                viewHolder.binding.detailArea.setVisibility(View.VISIBLE);
                viewHolder.binding.seperatorView.setVisibility(View.VISIBLE);
            }

            if (generalFunc.isRTLmode()) {
                viewHolder.binding.indicatorImg.setRotation(180);
            }

            viewHolder.binding.datarea.setOnClickListener(view -> {
                if (mItemClickListener != null) {
                    mItemClickListener.onItemClickList(position);
                }
            });

            manageBtn.setOnClickListener(view -> {
                if (mItemClickListener != null) {
                    mItemClickListener.onItemClickList(position);
                }
            });

        } else {
            this.footerHolder = (FooterViewHolder) holder;
        }
    }

    @Override
    public int getItemViewType(int position) {
        if (isPositionFooter(position) && isFooterEnabled) {
            return TYPE_FOOTER;
        }
        return TYPE_ITEM;
    }

    private boolean isPositionFooter(int position) {
        return position == list.size();
    }

    // Return the size of your itemsData (invoked by the layout manager)
    @Override
    public int getItemCount() {
        if (isFooterEnabled) {
            return list.size() + 1;
        } else {
            return list.size();
        }
    }

    @SuppressLint("NotifyDataSetChanged")
    public void addFooterView() {
        this.isFooterEnabled = true;
        notifyDataSetChanged();
        if (footerHolder != null) {
            footerHolder.progressContainer.setVisibility(View.VISIBLE);
        }
    }

    public void removeFooterView() {
        if (footerHolder != null)
            footerHolder.progressContainer.setVisibility(View.GONE);
    }

    public interface OnItemClickListener {
        void onItemClickList(int position);
    }

    protected static class ViewHolder extends RecyclerView.ViewHolder {

        private final ListOfDocItemDesignBinding binding;

        private ViewHolder(ListOfDocItemDesignBinding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }
    }

    protected static class FooterViewHolder extends RecyclerView.ViewHolder {
        private final LinearLayout progressContainer;

        public FooterViewHolder(View itemView) {
            super(itemView);
            progressContainer = itemView.findViewById(R.id.progressContainer);
        }
    }
}
