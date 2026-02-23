package com.adapter.files;

import android.annotation.SuppressLint;
import android.content.Context;
import android.graphics.Bitmap;
import android.graphics.Color;
import android.net.Uri;
import android.text.TextUtils;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.Filter;
import android.widget.Filterable;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.RelativeLayout;

import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;

import com.act.SearchLocationActivity;
import com.general.files.GeneralFunctions;
import com.google.gson.Gson;
import com.google.gson.reflect.TypeToken;
import com.buddyverse.main.R;
import com.model.ContactModel;
import com.utils.CommonUtilities;
import com.utils.LoadImage;
import com.utils.Logger;
import com.utils.Utils;
import com.view.CreateRoundedView;
import com.view.MTextView;
import com.view.SelectableRoundedImageView;

import java.util.ArrayList;
import java.util.List;
import java.util.Locale;

public class BookSomeOneContactListAdapter extends RecyclerView.Adapter<RecyclerView.ViewHolder> implements Filterable {

    private final Context mContext;
    private ItemClickListener clickListener;

    public String selectedNum;
    private final List<ContactModel> list;
    private List<ContactModel> contactListFiltered;

    private static final int SECTION_VIEW = 0, CONTENT_VIEW = 1, TYPE_FOOTER = 2;

    // paging
    private boolean isFooterEnabled;
    private FooterViewHolder footerHolder;
    private final int btnRadius;

    public BookSomeOneContactListAdapter(Context mContext, List<ContactModel> list, GeneralFunctions generalFunctions, boolean isFooterEnabled) {
        this.list = list;
        this.contactListFiltered = list;
        this.mContext = mContext;
        this.isFooterEnabled = isFooterEnabled;

        Gson gson = new Gson();
        String data1 = generalFunctions.retrieveValue(Utils.BFSE_SELECTED_CONTACT_KEY);
        ContactModel contactdetails = gson.fromJson(data1, new TypeToken<ContactModel>() {
                }.getType()
        );

        selectedNum = (contactdetails != null && Utils.checkText(contactdetails.mobileNumber)) ? contactdetails.mobileNumber : "";
        btnRadius = Utils.dipToPixels(mContext, R.dimen._17sdp);
    }

    @NonNull
    @Override
    public RecyclerView.ViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        if (viewType == SECTION_VIEW) {
            return new SectionHeaderViewHolder(LayoutInflater.from(parent.getContext()).inflate(R.layout.item_book_someone_contacts_header, parent, false));
        } else if (viewType == TYPE_FOOTER) {
            return new FooterViewHolder(LayoutInflater.from(parent.getContext()).inflate(R.layout.footer_list, parent, false));
        } else {
            return new ViewHolder(LayoutInflater.from(mContext).inflate(R.layout.item_book_someone_contact_design, parent, false));
        }
    }

    @Override
    public void onBindViewHolder(@NonNull RecyclerView.ViewHolder holder, final int position) {
        setData(holder, position);
    }


    private void setData(RecyclerView.ViewHolder holder, final int position) {
        if (SECTION_VIEW == getItemViewType(position)) {
            final ContactModel item = contactListFiltered.get(position);
            final SectionHeaderViewHolder viewHolder = (SectionHeaderViewHolder) holder;
            viewHolder.headerTitleTxt.setText(item.hederName);

        } else if (CONTENT_VIEW == getItemViewType(position)) {
            final ContactModel item = contactListFiltered.get(position);

            final ViewHolder viewHolder = (ViewHolder) holder;

            viewHolder.contactNameTxt.setText(item.nameLbl);
            viewHolder.nameTxt.setText(item.nameChar);
            viewHolder.contactNoTxt.setText(item.mobileNumber);
            if (Utils.checkText(item.mobileNumber)) {
                viewHolder.contactNoTxt.setVisibility(View.GONE);
            }

            int _color = mContext.getResources().getColor(R.color.white);

            new CreateRoundedView(Color.parseColor(item.colorVal), btnRadius, 1, _color, viewHolder.contactProfileImgView);
            new CreateRoundedView(Color.parseColor(item.colorVal), btnRadius, 1, _color, viewHolder.nameTxt);

            String vContactID = item.id;

            int color = mContext.getResources().getColor(R.color.appThemeColor_1);

            if (mContext instanceof SearchLocationActivity && item.mobileNumber.equalsIgnoreCase(selectedNum)) {
                viewHolder.imgSelected.setVisibility(View.VISIBLE);
            } else {
                viewHolder.imgSelected.setVisibility(View.INVISIBLE);
                int color_ = mContext.getResources().getColor(R.color.black);
                viewHolder.contactNameTxt.setTextColor(color_);
                viewHolder.imgSelected.setColorFilter(color_);
            }

            if (vContactID.equalsIgnoreCase("0")) {
                viewHolder.contactProfileImgView.setVisibility(View.GONE);
                viewHolder.nameTxt.setVisibility(View.GONE);
                viewHolder.imgSelected.setVisibility(View.INVISIBLE);
                viewHolder.addArea.setVisibility(View.VISIBLE);
                viewHolder.rl_book_someone.setVisibility(View.GONE);
                viewHolder.contactNameTxt.setTextColor(color);
            } else if (Utils.checkText(item.photo)) {
                Bitmap bitmap = Utils.fromBase64(item.photo);
                if (bitmap != null) {
                    viewHolder.nameTxt.setVisibility(View.GONE);
                    viewHolder.contactProfileImgView.setImageBitmap(bitmap);
                } else {
                    viewHolder.nameTxt.setVisibility(View.VISIBLE);
                }
            } else {

                viewHolder.nameTxt.setVisibility(View.VISIBLE);
                viewHolder.contactProfileImgView.setVisibility(View.GONE);
                viewHolder.contactProfileImgView.setImageURI(null);

                new LoadImage.builder(LoadImage.bind(item.photoURI != null && item.photoURI.startsWith("http") ? item.photoURI : CommonUtilities.USER_PHOTO_PATH), viewHolder.contactProfileImgView).setPicassoListener(new LoadImage.PicassoListener() {
                    @Override
                    public void onSuccess() {
                        if (item.photoURI != null && !item.photoURI.equals("") && item.photoURI.startsWith("http")) {
                            viewHolder.contactProfileImgView.setVisibility(View.VISIBLE);
                            viewHolder.nameTxt.setVisibility(View.GONE);
                        } else {
                            viewHolder.nameTxt.setVisibility(View.VISIBLE);
                            viewHolder.contactProfileImgView.setVisibility(View.GONE);
                            viewHolder.contactProfileImgView.setImageURI(null);
                        }
                    }

                    @Override
                    public void onError() {
                        if (item.photoURI != null && !item.photoURI.equals("") && Uri.parse(item.photoURI) != null && !item.photoURI.startsWith("http")) {
                            viewHolder.contactProfileImgView.setVisibility(View.VISIBLE);
                            viewHolder.nameTxt.setVisibility(View.GONE);
                            viewHolder.contactProfileImgView.setImageURI(Uri.parse(item.photoURI));
                        } else {
                            viewHolder.nameTxt.setVisibility(View.VISIBLE);
                            viewHolder.contactProfileImgView.setVisibility(View.GONE);
                            viewHolder.contactProfileImgView.setImageURI(null);
                        }

                    }
                }).build();

            }

            viewHolder.showContactArea.setOnClickListener(view -> {
                if (clickListener != null) {
                    clickListener.setSelected(position, item.id);
                }

                notifyItemChanged(position);

            });

        } else if (TYPE_FOOTER == getItemViewType(position)) {
            this.footerHolder = (FooterViewHolder) holder;
        }

    }

    private boolean isPositionFooter(int position) {
        return position == contactListFiltered.size();
    }

    // Return the size of your itemsData (invoked by the layout manager)
    @Override
    public int getItemCount() {
        if (isFooterEnabled) {
            return contactListFiltered.size() + 1;
        } else {
            return contactListFiltered.size();
        }

    }

    public void onClickListener(ItemClickListener itemClickListener) {
        this.clickListener = itemClickListener;
    }

    @Override
    public Filter getFilter() {
        return new Filter() {
            @Override
            protected FilterResults performFiltering(CharSequence charSequence) {
                List<ContactModel> filteredList = new ArrayList<>();

                String charString = charSequence.toString();
                if (charString.isEmpty()) {
                    filteredList = list;
                } else {
                    String lastHeader = "";
                    for (ContactModel row : list) {
                        String header = "";

                        if (Utils.checkText(row.nameLbl)) {
                            header = String.valueOf(row.nameLbl.charAt(0)).toUpperCase(Locale.US);
                        }

                        String listName = row.nameLbl.toLowerCase(Locale.US);
                        String listNum = row.mobileNumber;
                        String typedName = charString.toLowerCase(Locale.US);

                        if (listName.startsWith(typedName) || listNum.startsWith(String.valueOf(charSequence))) {
                            if (!TextUtils.equals(lastHeader, header)) {
                                lastHeader = header;
                                filteredList.add(new ContactModel(header, true));
                            }
                            filteredList.add(row);
                        }
                    }

                }

                FilterResults filterResults = new FilterResults();
                filterResults.values = filteredList;
                return filterResults;
            }

            @Override
            protected void publishResults(CharSequence charSequence, FilterResults filterResults) {
                Logger.d("SearchContact", "filteredList" + filterResults.values);
                if (filterResults.values != null)
                    contactListFiltered = (ArrayList<ContactModel>) filterResults.values;
                notifyDataSetChanged();
            }
        };
    }

    public interface ItemClickListener {
        void setSelected(int position, String rowId);
    }

    private static class ViewHolder extends RecyclerView.ViewHolder {
        SelectableRoundedImageView contactProfileImgView;
        MTextView contactNameTxt, nameTxt;
        MTextView contactNoTxt;
        LinearLayout showContactArea, addArea;
        ImageView addImgView, imgSelected;
        RelativeLayout rl_book_someone;

        public ViewHolder(View itemView) {
            super(itemView);

            imgSelected = (ImageView) itemView.findViewById(R.id.imgSelected);
            addImgView = (ImageView) itemView.findViewById(R.id.addImgView);
            addArea = (LinearLayout) itemView.findViewById(R.id.addArea);
            contactNameTxt = (MTextView) itemView.findViewById(R.id.contactNameTxt);
            contactNoTxt = (MTextView) itemView.findViewById(R.id.contactNoTxt);
            nameTxt = (MTextView) itemView.findViewById(R.id.nameTxt);
            showContactArea = (LinearLayout) itemView.findViewById(R.id.showContactArea);
            contactProfileImgView = (SelectableRoundedImageView) itemView.findViewById(R.id.contactProfileImgView);
            rl_book_someone = (RelativeLayout) itemView.findViewById(R.id.rl_book_someone);

        }

    }

    private static class SectionHeaderViewHolder extends RecyclerView.ViewHolder {
        MTextView headerTitleTxt;

        public SectionHeaderViewHolder(@NonNull View itemView) {
            super(itemView);
            headerTitleTxt = (MTextView) itemView.findViewById(R.id.headerTitleTxt);
        }
    }

    @Override
    public int getItemViewType(int position) {
        if (isPositionFooter(position) && isFooterEnabled) {
            return TYPE_FOOTER;
        } else if (contactListFiltered.get(position).isSection) {
            return SECTION_VIEW;
        } else {
            return CONTENT_VIEW;
        }
    }


    @SuppressLint("NotifyDataSetChanged")
    public void addFooterView() {
//        Logger.d("Footer", "added");
        this.isFooterEnabled = true;
        notifyDataSetChanged();
        if (footerHolder != null)
            footerHolder.progressArea.setVisibility(View.VISIBLE);
    }

    public void removeFooterView() {
//        Logger.d("Footer", "removed");
        if (footerHolder != null)
            footerHolder.progressArea.setVisibility(View.GONE);
//        footerHolder.progressArea.setPadding(0, -1 * footerView.getHeight(), 0, 0);
    }

    private static class FooterViewHolder extends RecyclerView.ViewHolder {
        LinearLayout progressArea;

        public FooterViewHolder(View itemView) {
            super(itemView);

            progressArea = (LinearLayout) itemView;

        }
    }

}
