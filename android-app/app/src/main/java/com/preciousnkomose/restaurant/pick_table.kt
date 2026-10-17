
package com.preciousnkomose.restaurant

import android.content.Intent
import android.os.Bundle
import android.view.View
import android.widget.TextView
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity
import androidx.core.content.ContextCompat
import com.google.android.material.card.MaterialCardView

class PickTableActivity : AppCompatActivity() {

    private var selectedTableId = 1
    private var selectedTableName = "Table 1"
    private var selectedTableCapacity = 4
    private var entireVenueSelected = false

    private val tableCapacities = mapOf(
        1 to 4,
        2 to 4,
        3 to 6,
        4 to 6,
        5 to 8,
        6 to 8
    )

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_pick_table)

        findViewById<View?>(R.id.btn_back)?.setOnClickListener {
            finish()
        }

        for (tableId in 1..6) {
            val card = findViewById<MaterialCardView?>(
                resources.getIdentifier(
                    "card_table_$tableId",
                    "id",
                    packageName
                )
            )

            card?.setOnClickListener {
                selectTable(tableId)
            }
        }

        findViewById<View?>(R.id.card_entire_venue)?.setOnClickListener {
            selectEntireVenue()
        }

        findViewById<View?>(R.id.btn_continue_reservation)?.setOnClickListener {
            continueToReservation()
        }

        updateSelectionDisplay()
    }

    private fun selectTable(tableId: Int) {
        selectedTableId = tableId
        selectedTableName = "Table $tableId"
        selectedTableCapacity = tableCapacities[tableId] ?: 4
        entireVenueSelected = false

        updateSelectionDisplay()
    }

    private fun selectEntireVenue() {
        entireVenueSelected = true
        selectedTableId = -1
        selectedTableName = "Entire Venue"
        selectedTableCapacity = 60

        updateSelectionDisplay()
    }

    private fun updateSelectionDisplay() {
        for (tableId in 1..6) {
            val card = findViewById<MaterialCardView?>(
                resources.getIdentifier(
                    "card_table_$tableId",
                    "id",
                    packageName
                )
            ) ?: continue

            val selected = !entireVenueSelected && selectedTableId == tableId

            card.strokeWidth = if (selected) 3 else 1
            card.strokeColor = ContextCompat.getColor(
                this,
                if (selected) R.color.table_selected else R.color.table_unselected
            )

            card.setCardBackgroundColor(
                ContextCompat.getColor(
                    this,
                    if (selected) R.color.table_selected_background
                    else android.R.color.white
                )
            )

            val badge = findViewById<TextView?>(
                resources.getIdentifier(
                    "badge_table_$tableId",
                    "id",
                    packageName
                )
            )

            badge?.text = if (selected) "Selected" else "${tableCapacities[tableId]} seats"
        }

        val venueCard = findViewById<MaterialCardView?>(R.id.card_entire_venue)
        venueCard?.strokeWidth = if (entireVenueSelected) 3 else 1
        venueCard?.strokeColor = ContextCompat.getColor(
            this,
            if (entireVenueSelected) R.color.table_selected
            else R.color.table_unselected
        )

        venueCard?.setCardBackgroundColor(
            ContextCompat.getColor(
                this,
                if (entireVenueSelected) R.color.table_selected_background
                else android.R.color.white
            )
        )

        findViewById<TextView?>(R.id.text_selected_table_summary)?.text =
            "$selectedTableName • Capacity: $selectedTableCapacity"
    }

    private fun continueToReservation() {
        if (selectedTableName.isBlank()) {
            Toast.makeText(
                this,
                "Please select a table first.",
                Toast.LENGTH_SHORT
            ).show()
            return
        }

        val intent = Intent(this, reserve_table::class.java).apply {
            putExtra("selected_table_id", selectedTableId)
            putExtra("selected_table_name", selectedTableName)
            putExtra("selected_table_capacity", selectedTableCapacity)
            putExtra("entire_venue_selected", entireVenueSelected)
        }

        startActivity(intent)
    }
}
